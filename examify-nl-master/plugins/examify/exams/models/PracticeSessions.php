<?php namespace Examify\Exams\Models;

use Model;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\Exams as Exams;
use Examify\Exams\Models\QuestionsAnswersLogs as QuestionsAnswersLogs;
use Examify\Exams\Models\Questions as Questions;
use Examify\Exams\Models\PracticeSessionsResultsLogs as PracticeSessionsResultsLogs;
use Examify\Exams\Models\QuestionsLocks as QuestionsLocks;
use Examify\Exams\Components\Charts as Charts;
use Carbon\Carbon as Carbon;
use Examify\Exams\Models\Homework as Homework;
use DB;

/**
 * Model
 */
class PracticeSessions extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_practice_sessions';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $jsonable = [
        'cached_answers',
        'cached_points',
        'question_id',
        'questions_locked',
        'child_ids'
    ];

    public $fillable = [
        'cached_answers',
        'cached_points',
        'questions_locked'
    ];

    protected $dates = ['created_at', 'updated_at', 'disabled_at', 'start_time', 'end_time'];

    public $belongsTo = [
        'user' => ['Examify\Exams\Models\Users'],
        'exam' => ['Examify\Exams\Models\Exams', 'exam_id'],
        'course' => ['Examify\Exams\Models\Courses', 'course_id'],
        'homework' => ['Examify\Exams\Models\Homework', 'homework_id']
    ];

    public function user()
    {
        return $this->belongsTo('Examify\Exams\Models\Users', 'user_id');
    }

    public function exam()
    {
        return $this->belongsTo('Examify\Exams\Models\Exams', 'exam_id');
    }

    public function homework()
    {
        return $this->belongsTo('Examify\Exams\Models\Homework', 'homework_id');
    }

    public function getHomework()
    {
        return $this->homework()->with('class')->first();
    }

    public function course()
    {
        return $this->belongsTo('Examify\Exams\Models\Courses', 'course_id');
    }

    public function resultsLogs()
    {
        return $this->hasMany('Examify\Exams\Models\PracticeSessionsResultsLogs', 'practice_session_id');
    }

    public function getResultsLogs()
    {
        return $this->resultsLogs()->get();
    }

    // check if it is over due date
    public function isOverDueDate()
    {
        if(!$this->homework_id)
        {
            return false;
        }

        return $this->due_date < Carbon::now();
    }

    public function getExam()
    {
        return $this->exam()->first();
    }

    public function getFullExamName()
    {
        $exam = $this->getExam();
        if(!$exam){ return ''; }

        return $exam->course->name . ' ' . $exam->course->level . ' ' . $exam->year . 'T' . $exam->tijdvak;
    }

    function getTimeSpent()
    {
        $h = $this->end_time->diffInHours($this->start_time);
        $m = $this->end_time->diffInMinutes($this->start_time);
        $m = $m - 60 * $h;
        return $h . 'h ' . $m . 'm';
    }

    // check if it is expired
    public function isExpired()
    {

        // in case it is finished, it is also expired
        if($this->finished){ return false; }

        if(!$this->time_limit_mins)
        {
            return false;
        }

        // it has a time limit when arrived here. In case the user is not started, return false
        if(!$this->started){ return false; }

        // it is started and not finished. Let's see if the current time is exceeding it.
        $timeleft = Carbon::now()->diffInSeconds($this->start_time->addMinutes($this->time_limit_mins), false);
        
        return $timeleft < 0;
    }

    public function isVisibleForUser($user)
    {
        if(empty($user)){ return false; }

        // check whether this user owns the session
        if($this->user_id == $user->id){ return true; }

        // check whether this user is an admin
        if($user->getUserSetting('isAdmin')){ return true; }
        if($user->getUserSetting('isSuperAdmin')){ return true; }

        // check whether this is practice session is related to a homework excersise
        if(!$this->homework_id){ return false; }

        // it is related to homework, let's check the related class and whether the user is a teacher of that class
        if($this->homework->class->hasTeacher($user)){ return true; }

        // the school admin should be allowed to see it
        if($user->isAdminForSchool($this->homework->class->school->id)){ return true; }

        return false;
    }

    public $hasMany = [
        'questionsAnswersLogs' => ['Examify\Exams\Models\QuestionsAnswersLogs', 'key' => 'practice_session_id'],
        'resultsLogs' => ['Examify\Exams\Models\PracticeSessionsResultsLogs', 'key' => 'practice_session_id']
    ];

    public function getQuestions()
    {

        // get the questions associated with this practice session
        if(!$this->question_id){
            return [];
        }

        return Questions::find($this->question_id);

    }

    public function getQuestionIDs()
    {
        return collect($this->question_id);
    }

    // wrapper functions
    public function getProgressColor()
    {

        // in case it is a finished examenmodus, return the mark color
        if(!$this->leermodus && $this->finished){
            $myMark = $this->mark;
            return Charts::getMarkColor($myMark, 10);
        }

        // always threat it as progress percentage
        $myPercentage = $this->getPercentageCompleted($this->finished);
        return Charts::getPercentageColor($myPercentage);

    }

    public function isFullExam()
    {
        if(!$this->exam_id){ return false; }

        // check if the exam_id is there, and number of questions are equal to the number of questions in the exam
        $q = $this->getQuestions();
        if(!$q){ return false; }

        $nq = $q->count();

        $expectednq = $this->exam->getNumberOfQuestions();

        return $nq == $expectednq;

    }

    public function getMarkForPoints($S, $L, $N)
    {
        $ratio = 9 * $S / $L;

        $standard   = $ratio + $N;
        $limit[1]   = 1 + $ratio * 2;
        $limit[2]   = 1 + $ratio * 0.5;
        $limit[3]   = 10 - ($L - $S) * (9 / $L) * 2;
        $limit[4]   = 10 - ($L - $S) * (9 / $L) * 0.5;

        if($N >= 1){
            return round(min($standard, $limit[1], $limit[4]), 1);
        }
        else {
            return round(max($standard, $limit[2], $limit[3]), 1);
        }
    }

    public function getMark()
    {

        // get the mark
        if(!$this->exam_id || !$this->finished){
            return false;
        }

        // compute the mark based on n-term
        $N = $this->exam->norm;

        // get the total points achieved and available
        $S = $this->totalpointsachieved;
        $L = $this->totalpointsavailable;
        return $this->getMarkForPoints($S, $L, $N);

    }

    public function getPercentageAnswered()
    {

        // get the percentage of answered questions
        if(!$questions = $this->getQuestions()){
            return [
                'value' => 0,
                'max' => 0
            ];
        }

        // get the number of questions
        $nQuestions = $questions->count();

        // get the percentage of answered questions
        $percentageCompleted = $this->getPercentageCompleted(false);

        // multiply by the number of questions to get the number of questions answered
        $nQuestionsAnswered = round($nQuestions * $percentageCompleted / 100);

        // get the number of answered questions
        return [
            'value' => $nQuestionsAnswered,
            'max' => $nQuestions
        ];

    }

    public function getPercentageCompleted($markFinishedAsCompleted = true)
    {

        // in case the session is finished, return 100
        if($this->finished && $markFinishedAsCompleted){
            return 100;
        }

        // get all questions associated with this practice session
        if(!$questions = $this->getQuestions()){
            return 0;
        }

        // get the total number of questions
        if(!$totalnr = $questions->count()){
            return 0;
        }

        // in case no questions are logged, return 0 percent completed
        $this->generateCachedAnswersProperty(true);
        $ca = collect($this->cached_answers);
        if(empty($ca)){ return; }

        // kick out the question ids for which the open answer is empty, since that one is then not really filled
        $finalAnswers = $ca->unique('question_id');
        foreach($finalAnswers as $key => $entry){
            if(!is_array($entry)){ continue; }
            if(!array_key_exists('open_answer', $entry)){ continue; }
            if($entry['open_answer'] === NULL){
                unset($finalAnswers[$key]);
            }
        }

        if(!count($finalAnswers)){ return 0; }
        $foundQuestionIDs = $finalAnswers->pluck('question_id');

        //$foundQuestionIDs = QuestionsAnswersLogs::whereRaw('(open_answer IS NOT NULL OR answer_id IS NOT NULL) AND practice_session_id = ?', $this->id)->get()->pluck('question_id')->unique();   ajskdlfjklj

        if(!$foundQuestionIDs){
            return 0;
        }

        // 100 percent completed if 0 unanswered questions are found
        if(!$unansweredQuestions = $questions->whereNotIn('id', $foundQuestionIDs)){
            return 100;
        }

        $totalanswered = $totalnr - $unansweredQuestions->count();

        // now return the percentage
        return $totalanswered * 100 / $totalnr;

    }

    public function getUnansweredQuestions()
    {

        // get all questions associated with this practice session
        if(!$questions = $this->getQuestions()){
            return [];
        }

        $myLockedIDs = collect($this->questions_locked);
        $filteredQuestions = $questions->whereNotIn('id', $myLockedIDs);

        $ca = collect($this->cached_answers);
        if(empty($ca)){ return $questions; }

        $foundQuestionIDs = $ca->pluck('question_id')->unique();
        $myq = $filteredQuestions->whereNotIn('id', $foundQuestionIDs->values());

        if(!$myq->count()){
            return [];
        }
        
        return $myq;

    }

    public function hasUnlockedQuestionsByText($thisText)
    {

        // first get the questions by text
        if(!$questions = $this->getQuestionsByText($thisText)){
            return false;
        }

        // all question ids that are locked
        $myLocksIDs = collect($this->questions_locked); 

        // check if some of these are unanswered
        $intersection = $questions->whereNotIn('id', $myLocksIDs);

        if($intersection->count()){
            return true;
        }

        return false;
    }

    public function getQuestionsByText($thisText)
    {
        // get all questions associated with this practice session
        if(!$questions = $this->getQuestions()){
            return [];
        }

        $textQuestionIDs = $thisText->questions->pluck('id');

        return $questions->whereIn('id', $textQuestionIDs);
    }

    public static function getActiveSession(Users $user, $exam_id, $practicemode)
    {

        $leermodus = $practicemode == 'leermodus';

        // check if there is already one with this 
        return $existing = PracticeSessions::where('user_id', $user->id)
                                    ->where('exam_id', $exam_id)
                                    ->where('leermodus', $leermodus)
                                    ->where('finished', false)
                                    ->first();
    }

    public function getPointsScored($question_id){

        if(!$this->finished){ return 0; }

        $cp = collect($this->cached_points);
        $log = $cp->firstWhere('question_id', $question_id);
        if(!$log){ return 0; }

        return $log['points'];

    }

    // check if the question is part of the session
    public function hasQuestionID($question_id)
    {
        $qids = collect($this->question_id);
        return $qids->contains($question_id);   
    }

    public function hasQuestion($question)
    {
        return $this->hasQuestionID($question->id);
    }

    public function finish($myController)
    {

        if($this->finished){
            return;
        }

        // update for the last time the logs to make sure all is updated (also after manual actions of giving points to open answers)
        $this->generateCachedAnswersProperty();

        // save the end time. In case the time_limit_mins is defined, it should be maximum the time limit mins.
        $thisTime = Carbon::now();
        if($this->time_limit_mins)
        {
            $maxEndTime = $this->start_time->addMinutes($this->time_limit_mins);

            if($thisTime > $maxEndTime){
                $thisTime = $maxEndTime;
            }
        }

        // store the end time
        $this->end_time = $thisTime;

        // check how many points are achieved. Loop over all the Questions in this Practice Session
        $questionIDs = $this->getQuestionIDs()->unique();

        // get all the questions
        $allQuestions = Questions::whereIn('id', $questionIDs)->get();

        // keep track of questions that should be manually checked
        $manualQuestionList     = array();
        $pointsScoredList       = array();   

        foreach($allQuestions as $thisQuestion){

            $pointsScored = $thisQuestion->getPointsScored($this->id);

            // in case the points scored is -1, skip it, since it means it cannot be checked automatically
            if($pointsScored == -1){

                // add this question to the list that should be manually checked by the user
                $manualQuestionList[] = $thisQuestion->id;
            }

            // keep track of the points scored in an array such that the function only has to be called once
            $pointsScoredList[$thisQuestion->id] = $pointsScored;

            // store points lost due to unanswered questions
            $pointsUnansweredList[$thisQuestion->id] = $thisQuestion->getPointsUnanswered($this->id);
        }

        if(!empty($manualQuestionList)){

            // show the questions that the user should check manually
            return [
                'valid' => false,
                'updateElement' => [
                    '#placeholder-full-session' => $myController->renderPartial('examifyHelpers/checkPracticeSessionManualQuestions', ['practiceSession' => $this, 'manualQuestionIDs' => $manualQuestionList])
                ],
                'scrollToElement' => '#placeholder-full-session'
            ];

        }

        // loop over the questions again
        foreach($allQuestions as $thisQuestion)
        {

            // store the results in the logs
            $myLog = new PracticeSessionsResultsLogs();

            // save all
            $myLog->practice_session_id     = $this->id;
            $myLog->question_id             = $thisQuestion->id;
            $myLog->question_type_id        = $thisQuestion->type->id;
            $myLog->points_achieved         = $pointsScoredList[$thisQuestion->id];
            $myLog->points_available        = $thisQuestion->points;
            $myLog->points_unanswered       = $pointsUnansweredList[$thisQuestion->id];
            $myLog->user_id                 = $this->user_id;

            // store the question information
            $thisText   = $thisQuestion->text;
            $thisExam   = $thisText->exam;
            $thisCourse = $thisExam->course;

            $myLog->course_id       = $thisCourse->id;
            $myLog->exam_id         = $thisExam->id;
            $myLog->text_id         = $thisText->id;
            $myLog->level           = $thisCourse->level;

            $myLog->save();

            // add the locked question
            $ql = collect($this->questions_locked);
            $ql->prepend($thisQuestion->id);
            $this->save();

            // save the cached answer
            QuestionsAnswersLogs::saveCachedAnswer($thisQuestion, $this->id);
        }

        // save that it is finished
        $this->totalpointsachieved = array_sum($pointsScoredList);
        $this->totalpointsunanswered = array_sum($pointsUnansweredList);

        // update the cached points property
        $this->generateCachedPointsProperty();

        // store the mark
        $this->finished = true;

        if($mark = $this->getMark()){
            $this->mark = $mark;
        }

        $this->save();

        // check if all the other siblings are also finished, and combine all these answers into one new practice session
        $this->combineSiblings();

        // return the update
        return [
            'valid' => true,
            'updateElement' => [
                    '#placeholder-full-session' => $myController->renderPartial('examifyHelpers/fullExamScroll', ['practiceSession' => $this])
                ],
            'call-js-function' => [
                'iniPagination' => false,
                'iniAllChartForms' => false,
                'iniAllProgressBarForms' => false,
            ],
            'scrollToElement' => '#placeholder-full-session'
        ];

    }

    public static function createSession(Users $user, $exam_id, $practicemode, $forceNew = false, $sessionName = '')
    {

        if(empty($sessionName))
        {
            return [
                'valid' => false,
                'message' => 'Geef deze oefensessie een naam.'
            ];
        }

        // check the practice mode
        if($practicemode != 'leermodus' && $practicemode != 'examenmodus')
        {
            return [
                'valid' => false,
                'message' => 'De oefenmodus kan alleen "leermodus" of "examenmodus" zijn.'
            ];
        }

        $leermodus = $practicemode == 'leermodus';

        // get the exam and validate it is available for the user
        $myExam = Exams::where('id', $exam_id)->availableForUser()->with('texts.questions', 'course')->first();

        if(!$myExam)
        {
            return [
                'valid' => false,
                'message' => 'Dit examen is niet beschikbaar of je hebt er geen toegang tot.'
            ];
        }

        $existing = PracticeSessions::getActiveSession($user, $exam_id, $practicemode);

        // in case it exists, show that it can be used already
        if(!$existing || $forceNew)
        {

            // create it
            $mySession = new PracticeSessions();

            $mySession->user_id = $user->id;
            $mySession->exam_id = $myExam->id;
            $mySession->course_id = $myExam->course->id;
            $mySession->leermodus = $leermodus;
            $mySession->finished = false;
            $mySession->name = $sessionName;

            // check the texts that are associated to this
            $examTexts = $myExam->texts;

            // error in case it is not available
            if(!$examTexts->count()){
                return [
                    'valid' => false,
                    'message' => 'Dit examen heeft nog geen teksten die eraan gekoppeld zijn.'
                ];
            }

            // error in case there are no questions
            $textQuestions = $examTexts->pluck('questions')->flatten();

            if(!$textQuestions->count())
            {
                return [
                    'valid' => false,
                    'message' => 'Er zijn nog geen vragen gekoppeld aan dit examen.'
                ];
            }

            // store all the ids of the questions
            $questionIDs = $textQuestions->pluck('id');
            $mySession->question_id = $questionIDs;

            // sum all the points to get the available points
            $availablePoints = $textQuestions->sum('points');

            $mySession->totalpointsavailable = $availablePoints;

            // save this session
            $mySession->save();

            return [
                'valid' => true,
                'message' => '',
                'message-info' => '',
                'session-link' => '/oefenen/mijn-oefensessie-' . $mySession->id
            ];

        }
        else {
            return [
                'valid' => false,
                'message-info' => '<input type="hidden" name="forceNew" value="1" />We hebben een zelfde oefensessie gevonden die je nog niet hebt afgerond. Ik wil: <div class="step-actions mt-3" ><a class="shadow-none waves-effect waves-dark btn btn-sm btn-primary next-step" href="/oefenen/mijn-oefensessie-' . $existing->id . '" >Verder gaan</a><button class="shadow-none waves-effect waves-dark btn btn-sm btn-primary previous-step" onclick="return selectExam($(this));">Een nieuwe starten</button></div>'
            ];
        }

    }

    public function getLogData()
    {
        return QuestionsAnswersLogs::where('practice_session_id', $this->id)->get();
    }

    public static function getListOfQuestions($practicesessions, $type_ids = [], $answer_types = [], $user_ids = [])
    { 

        if(!$practicesessions->count())
        {
            return [];
        }  

        if(!empty($user_ids))
        {
            $practicesessions = $practicesessions->whereIn('user_id', $user_ids);
            if(!$practicesessions->count()){ return []; }
        }

        $questionIDs = $practicesessions->pluck('question_id')->flatten()->unique()->values();
        $myQuestions = Questions::with('text', 'text.exam')->find($questionIDs);

        // get the texts
        $examTexts = $myQuestions->pluck('text')->unique()->sortBy('myorder')->sortBy('exam_id')->sortBy('exam.tijdvak')->sortByDesc('exam.year');

        // get the number of students that finished the practice sessions
        $nStudents = $practicesessions->unique('user_id')->count();

        // empty the list of questions
        $listofquestions = [];

        // for each of the practice sessions, update the cached points and cached answers if needed (this is due to legacy)
        foreach($practicesessions as $ps){
            $ps->generateCachedAnswersProperty(true);
            $ps->generateCachedPointsProperty(true);
        }

        // flatten the cached points (which contain the ids of the results logs)
        $cached_points = $practicesessions->pluck('cached_points');
        $cached_points = $cached_points->flatten(1);

        $cached_answers = $practicesessions->pluck('cached_answers');
        $cached_answers = $cached_answers->flatten(1);

        // get the associated results logs
        $resultslogsbase = PracticeSessionsResultsLogs::find($cached_points->pluck('log_id'));
        $answerlogsbase  = QuestionsAnswersLogs::with('user')->find($cached_answers->pluck('log_id'))->where('is_final', true)->sortByDesc('points');

        // loop over the text and add question to it
        foreach($examTexts as $text)
        {
            $questions = $text->questions->whereIn('id', $questionIDs);

            // filter by type ids
            if(!empty($type_ids))
            {
                $questions = $questions->whereIn('type_id', $type_ids);
            }

            // for each of the question, add it to the list
            foreach($questions as $question)
            {

                // in case the answer types are given, check it
                if(!empty($answer_types) && !in_array($question->answer_type, $answer_types))
                {
                    continue;
                }

                // get the total points answered etc
                //$logs = PracticeSessionsResultsLogs::where('question_id', $question->id)->whereIn('practice_session_id', $practicesessions->pluck('id')->values()->all())->get();

                $logs = $resultslogsbase->where('question_id', $question->id);

                $pa = $logs->sum('points_achieved') / $nStudents;
                $pu = $logs->sum('points_unanswered') / $nStudents;
                $pavailable = $logs->sum('points_available') / $nStudents;
                $pw = $pavailable - $pa - $pu;

                // get the answers that were given
                $answerlogs = $answerlogsbase->where('question_id', $question->id);

                //$answerlogs = QuestionsAnswersLogs::whereIn('practice_session_id', $practicesessions->pluck('id')->values())->where('question_id', $question->id)->where('is_final', true)->orderByDesc('points')->with('user')->get();

                $answersToStore = [];

                // in case of an open item, list the open answers
                switch ($question->answer_type) {
                    case 'open':
                        $answersToStore = $answerlogs;
                        break;

                    case 'multiplechoice_single':
                        // do the counts per answer id
                        $groups = $answerlogs->groupBy('answer_id');
                        foreach($groups as $answer_id => $group)
                        {
                            $answersToStore[$answer_id] = $group->count();
                        }
                        break;

                    case 'multiplechoice_multiple':
                        $groups = $answerlogs->groupBy('answer_id');
                        foreach($groups as $answer_id => $group)
                        {
                            $answersToStore[$answer_id] = $group->count();
                        }
                        break;

                    case 'truefalse':
                        // do the counts per answer id and per value
                        $groups = $answerlogs->groupBy('answer_id');
                        foreach($groups as $answer_id => $group)
                        {
                            // check whether the answer should be true or false first
                            $refpoints = Answers::where('id', $answer_id)->first()->points;

                            $ntrue = max($group->where('points', $refpoints)->count() / $nStudents * 100, 0);
                            $nfalse = max($group->where('points', !$refpoints)->count() / $nStudents * 100, 0);
                            $nunans = 100 - $ntrue - $nfalse;
                            $answersToStore[$answer_id] = collect(array(
                                'ntrue' => $ntrue,
                                'nfalse' => $nfalse,
                                'nunans' => $nunans
                            ));
                        }
                        $answersToStore = collect($answersToStore);
                        break;
                    
                    default:
                        $answersToStore = 'something went wrong in the answer type. It is not recognized.';
                        # code...
                        break;
                }

                // note that the averages are given
                $listofquestions[] = [
                    'label' => $text->exam->year . '-' . $text->exam->tijdvak . '/T' . $text->myorder . '/Q' . $question->question_nr,
                    'pointsAchieved' => $pa, 
                    'pointsUnanswered' => $pu,
                    'pointsWrongAnswered' => $pw,
                    'pointsAvailable' => $pavailable,
                    'text' => $text,
                    'question' => $question,
                    'textid' => $text->id,
                    'answers' => $answersToStore,
                ];
            }
        }

        $listofquestions = collect($listofquestions);

        if(!$listofquestions->count()){ return []; }

        // group by text and perform the counts
        $listofquestions_bytext = $listofquestions->groupBy('textid');
        $listofquestions_maxpoints = $listofquestions->max('pointsAvailable');

        // for each of the questions, list the points seprately
        foreach($listofquestions_bytext as $textid => $list)
        {
            $mypoints['pointsAchieved'] = $list->sum('pointsAchieved');
            $mypoints['pointsUnanswered'] = $list->sum('pointsUnanswered');
            $mypoints['pointsAvailable'] = $list->sum('pointsAvailable');
            $mypoints['pointsWrongAnswered'] = $list->sum('pointsWrongAnswered');

            $temp[$textid] = collect($mypoints);
        }

        $pointspertext = collect($temp);

        $maxpointspertext =  $pointspertext->max('pointsAvailable');
        $texts = $listofquestions->pluck('text')->unique()->keyBy('id');

        // return all
        return [
            'bytext' => $listofquestions_bytext,
            'pointspertext' => $pointspertext,
            'maxpointspertext' => $maxpointspertext,
            'texts' => $texts
        ];

    }

    public function combineSiblings()
    {
        // combine the siblings if they are all finished as well.
        $tocombine = $this->getSiblingsToCombine();

        if(!$tocombine->count()){ return; }

        // it reached this part of the code, so let's combine them
        $ps = new PracticeSessions();

        $ps->user_id = $this->user_id;
        
        // update the ids of the children
        $ids = $tocombine->pluck('id')->values()->all();
        $ps->child_ids = $ids;

        // combine the total available points
        $ps->totalpointsavailable = $tocombine->sum('totalpointsavailable');
        $ps->totalpointsachieved = $tocombine->sum('totalpointsachieved');
        $ps->totalpointsunanswered = $tocombine->sum('totalpointsunanswered');
        $ps->exam_id = $this->partial_exam_id;
        $ps->homework_id = $this->homework->parent_id;
        $ps->start_time = $tocombine->sortBy('start_time')->pluck('start_time')->first();
        $ps->end_time = $tocombine->sortByDesc('end_time')->pluck('end_time')->first();
        $ps->due_date = $tocombine->sortByDesc('due_date')->pluck('due_date')->first();

        $ps->leermodus = $tocombine->first()->leermodus;
        $ps->started = true;
        $ps->finished = true;
        $ps->question_id = $tocombine->pluck('question_id')->flatten();

        // get the name from the exam
        $ps->name = $this->homework->name;
        $ps->course_id = $tocombine->first()->course_id;

        $ps->save();

        // compute the mark
        $ps->mark = $ps->getMark();
        $ps->save();

        foreach($tocombine as $p)
        {
            $p->parent_id = $ps->id;
        }

        // generate the cached answers and cached points for this session
        $ps->generateCachedAnswersProperty();
        $ps->generateCachedPointsProperty();

    }

    public function getSiblingsToCombine()
    {
        $siblings = $this->getSiblings();
        $n_siblings = $siblings->count();
        $n_finished_siblings = $siblings->where('finished', true)->count();

        if($n_siblings && $n_siblings == $n_finished_siblings){
            return $siblings;
        }

        return collect([]);

    }

    public function getSiblings()
    {
        // the practicesessions can be part of a homework session that is a whole exam split into multiple sessions. This function returns the siblings if they exist, and oterhwise it simply returns an empty collection.
        $result = collect([]); 

        if(!$this->homework_id){ return $result; }
        if(!$this->partial_exam_id){ return $result; }

        // check if the homework is a split exam
        $hw = $this->homework;
        if(!$hw->split_exam){ return $result; }

        // get the child homework elements
        $hwsiblings = $hw->getSiblings();
        if(!$hwsiblings->count()){ return $result; }

        // get the id of the homework siblings
        $hwid = $hwsiblings->pluck('id')->values()->all();

        // find practicesessions related to this user where homework id is one of those
        $allsessions = $this->user->practiceSessions()->get()->whereIn('homework_id', $hwid);
        return $allsessions;
    }

    public function getQuestionsAnswersLogsWithQuestions()
    {
        // get the question answers logs for this user, based on the cached answers
        $this->generateCachedAnswersProperty(true);

        $ca = collect($this->cached_answers);
        if(empty($ca)){ return []; }

        return QuestionsAnswersLogs::with('question')->find($ca->pluck('log_id'))->sortBy('created_at');
    }

    public function generateCachedAnswersProperty($onlyifempty = false)
    {
        if($onlyifempty && !empty($this->cached_answers)){ return; }

        $ps = PracticeSessions::find($this->id);

        // in case this is a main practice sessions consisting of child sessions, generate the cached answers via the children.
        if(!empty($this->child_ids))
        {
            $children = PracticeSessions::find($this->child_ids);
            $ca = collect([]);
            foreach($children as $c)
            {
                $c->generateCachedAnswersProperty($onlyifempty);
                $ca->push(collect($c->cached_answers));
            }

            $ca = $ca->flatten(1);

            $ps->update(['cached_answers' => $ca]);
            // also update it of the current object
            $this->cached_answers = $ca;
            return;
        }


        $qals = QuestionsAnswersLogs::where('practice_session_id', $this->id)->orderByDesc('id')->get();

        if(!$qals->count()){ 
            $ps->update(['cached_answers' => ['question_id' => -1, 'answer_id' => -1, 'points' => 0, 'log_id' => 0]]); 
            return;
        }

        // pluck all the relevant information
        $a = $qals->map( function($item, $key) {
            if($item->answer_id){ 
                $new = [ 
                    'question_id' => $item->question_id, 
                    'answer_id' => $item->answer_id, 
                    'points' => $item->points, 
                    'log_id' => $item->id 
                ];
            }
            else {
                $new = [ 
                    'question_id' => $item->question_id, 
                    'open_answer' => $item->open_answer,
                    'points' => $item->points,
                    'log_id' => $item->id
                ];
            }

            return $new;
            
        });
        
        $ps->update(['cached_answers' => collect($a)]);

        // also update it of the current object
        $this->cached_answers = collect($a);
    }

    public function generateCachedPointsProperty($onlyifempty = false)
    {
        if($onlyifempty && !empty($this->cached_points)){ return; }

        $ps = PracticeSessions::find($this->id);

        // in case this is a main practice sessions consisting of child sessions, generate the cached answers via the children.
        if(!empty($this->child_ids))
        {
            $children = PracticeSessions::find($this->child_ids);
            $cp = collect([]);
            foreach($children as $c)
            {
                $c->generateCachedPointsProperty($onlyifempty);
                $cp->push(collect($c->cached_points));
            }

            $cp = $cp->flatten(1);

            $ps->update(['cached_points' => $cp]);
            // also update it of the current object
            $this->cached_points = $cp;
            return;
        }

        // only do this if the practice session is finished
        $psrls = PracticeSessionsResultsLogs::where('practice_session_id', $this->id)->get();

        if(!$psrls->count()){ 

            $ps->update(['cached_points' => [
                'question_id' => 0,
                'points' => 0,
                'log_id' => 0
            ]]);
            return;
        }

        // it might be possible that there are duplicates, so make sure to take only the unique question ids
        $psrls = $psrls->unique('question_id');

        // pluck all the relevant information
        $a = $psrls->map( function($item, $key) {
            return [
                'question_id' => $item->question_id,
                'points' => $item->points_achieved,
                'log_id' => $item->id
            ];
        });
        
        $ps->update(['cached_points' => collect($a)]);

        // also update it of the current object
        $this->cached_points = collect($a);
    }
}
