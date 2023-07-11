<?php namespace Examify\Exams\Models;

use Model;
use Examify\Exams\Models\Exams as Exams;
use Examify\Exams\Models\Courses as Courses;
use Examify\Exams\Models\Answers as Answers;
use Examify\Exams\Models\Texts as Texts;
use Examify\Exams\Models\QuestionsAnswersLogs as QuestionsAnswersLogs;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\PracticeSessions as PS;

// validation
use October\Rain\Exception\ValidationException as ValidationException;

/**
 * Model
 */
class Questions extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_questions';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    // to enforce order by
    public function answers()
    {
        return $this->hasMany('Examify\Exams\Models\Answers', 'question_id')->orderBy('myorder');
    }

    public function locks()
    {
        return $this->hasMany('Examify\Exams\Models\QuestionsLocks', 'question_id');
    }

    public function text()
    {
        return $this->belongsTo('Examify\Exams\Models\Texts', 'text_id');
    }

    public $hasMany = [
        'answers' => ['Examify\Exams\Models\Answers', 'key' => 'question_id'],
        'locks'   => ['Examify\Exams\Models\QuestionsLocks', 'key' => 'question_id']
    ];

    public $jsonable = ['answers_content', 'question_builder'];

    public $belongsTo = [
        'type' => ['Examify\Exams\Models\QuestionTypes'],
        'text' => ['Examify\Exams\Models\Texts']
    ];

    public function getCourseAttribute()
    {
        return $this->text->exam->course->name;
    }

    public function getYearAttribute()
    {
        return $this->text->exam->year;
    }

    public function getTijdvakAttribute()
    {
        return $this->text->exam->tijdvak;
    }

    public function course()
    {
        return $this->text()->with('course')->get()->pluck('text.course');
    }

    // check if the question is locked in leermodus
    public function isLocked($psid)
    {
        $ps = PS::find($psid);
        $ql = collect($ps->questions_locked);
        return $ql->contains($this->id);
    }

    public static function getNamesForAnswerTypes()
    {

        return [
            'open' => 'Open vraag',
            'multiplechoice_single' => 'Multiple choice',
            'multiplechoice_multiple' => 'Multiple choice (>1 goed antwoord)',
            'truefalse' => 'Wel / niet'
        ];

    }

    // get the options in the ExamOptions dropdown
    public function getTextOptions() {

        $mytexts = Texts::all();

        $resultarray = [];

        // first get each text
        foreach($mytexts as $text){

            // get the associated exam
            $exam = $text->exam;
            $thiscourse = $exam->course;
            $resultarray[$text->id] = $thiscourse->level . ' / ' . $thiscourse->name . ' / ' . $exam->year . ' / tijdvak ' . $exam->tijdvak . ' / Tekst ' . $text->myorder . ': ' . $text->name;
        }

        return $resultarray;

    }

    public function getPointsUnanswered($PSID)
    {
        // get the number of points scored for this Practice Session ID and question combination
        $myAnswer = QuestionsAnswersLogs::getCachedAnswer($this, $PSID);

        // different strategies for answer types

        if($this->answer_type == 'open')
        {
            // in case no answer is given, always 0 points are given to it
            if($myAnswer === null || $myAnswer['value'] === null){
                
                // return all the points of this question
                return $this->points;

            } 

            return 0;

        }
        elseif($this->answer_type == 'truefalse')
        {
            // standard strategy: get the total number of points that are stored for this question, and subtract 1 point for each wrong answer
            $MaxPoints = $this->points;
            $lostPoints = 0;

            // in case not only correct answers are count, every not answered question is a point loss
            if(!$this->count_only_correct)
            {
                foreach($this->answers as $answer)
                {
                    // in case the answer is not given, it is always minus 1
                    if(!array_key_exists($answer->id, $myAnswer)){
                        $lostPoints += 1;
                    }

                }
            }
            else {
                // count if positives are answered compared to the expected positives
                $nPositivesGiven = 0;
                $nPositivesExpected = 0;

                foreach($this->answers as $answer)
                {
                    $nPositivesExpected += $answer->points;

                    if($myAnswer[$answer->id] == 1)
                    {
                        $nPositivesGiven += 1;
                    }
                }
                // all more or less positives given is a los of points due to unanswered or too much answered questions
                $lostPoints = abs($nPositivesGiven - $nPositivesExpected);

            }

            // limit it to 0 points per question
            $lostPoints = min($lostPoints, $MaxPoints);
            return $lostPoints;

        }
        elseif($this->answer_type == 'multiplechoice_single' || $this->answer_type == 'multiplechoice_multiple')
        {
            // the points are equal to the points of the answer
            if($myAnswer === null || !($myAnswer = Answers::find($myAnswer))){
                // the answer is not found in the cache, which means it is unanswered
                return $this->points; 
            }

            return 0;
        }
    }

    public function getPointsScored($PSID)
    {
        // get the number of points scored for this Practice Session ID and question combination
        $myAnswer = QuestionsAnswersLogs::getCachedAnswer($this, $PSID);

        // different strategies for answer types

        if($this->answer_type == 'open')
        {
            // in case no answer is given, always 0 points are given to it
            if($myAnswer === null){
                
                // log it
                if(!$user = Users::getUser()){
                    return 0;
                }

                $newLog = new QuestionsAnswersLogs();
                $newLog->user_id = $user->id;
                $newLog->question_id = $this->id;
                $newLog->points = 0;
                $newLog->practice_session_id = $PSID;
                $newLog->save();

                return 0;
            } 

            if($myAnswer['points'] === null)
            {
                return -1;
            }

            // get the points assigned to this answer
            return $myAnswer['points'];

        }
        elseif($this->answer_type == 'truefalse')
        {
            // standard strategy: get the total number of points that are stored for this question, and subtract 1 point for each wrong answer
            $MaxPoints = $this->points;
            $myPoints = $MaxPoints;

            // default (count only correct == 0)
            if(!$this->count_only_correct)
            {
                // in case the points given by the user does not match the points given to an answer, it is incorrect
                foreach($this->answers as $answer)
                {
                    // in case the answer is not given, it is always minus 1
                    if(!array_key_exists($answer->id, $myAnswer)){
                        $myPoints -= 1;
                        continue;
                    }

                    // in case it does not match exactly the answer
                    $myPoints -= $myAnswer[$answer->id] !== $answer->points;
                }
            }
            else {

                $MaxPoints = $this->points;
                $myPoints = $MaxPoints;

                // loop over the answers, only count it as wrong if the answer should have points assigned
                // count the number of positives
                $nPositivesGiven = 0;
                $nPositivesExpected = 0;
                foreach($this->answers as $answer)
                {

                    // check if points are assigned to this one
                    $nPositivesExpected += $answer->points;

                    // in case the answer is not given, it is always minus 1
                    if($myAnswer[$answer->id] === null || $myAnswer[$answer->id] == 0 ){
                        continue;
                        // this will be later cached with the false positives
                    }

                    if($myAnswer[$answer->id] == 1)
                    {
                        $nPositivesGiven++;

                        // in case it is wrong, decrease the points
                        if($myAnswer[$answer->id] !== $answer->points)
                        {
                            $myPoints -= 1;
                        }

                        continue;
                    }
                }

                // check if the final number of positives given equals the number of positives expected. If not, reduce the points by that one.
                $myPoints -= max(($nPositivesExpected - $nPositivesGiven), 0);

            }

            // limit it to 0 points per question
            $myPoints = max($myPoints, 0);
            return $myPoints;

        }
        elseif($this->answer_type == 'multiplechoice_single' || $this->answer_type == 'multiplechoice_multiple')
        {
            // the points are equal to the points of the answer
            if($myAnswer === null || !($myAnswer = Answers::find($myAnswer))){
                // the answer is not found in the cache, which means it is unanswered
                return 0; 
            }

            // else return the points belonging to this answer entry
            return $myAnswer->points * $this->points;
        }

    }

    // couple the related items
    private function coupleTheAnswersInDatabase()
    {
        // There are changes when the server reached this point. This means that we should check whether answers should be added to the Answers table, or answers should be removed

        // first get all the answers already stored to this one
        $storedanswers = $this->answers();

        // in case the answer type is open, remove all answers from the answer table related to this question and return
        if($this->answer_type == 'open'){
            $storedanswers->delete();
            return;
        }

        // the elements to add is equal to the number of answers minus the number of answers already stored in the database
        $nrofanswersstored = $storedanswers->count();

        $count = 0;
        $hasavalidanswer = false;

        // loop over the answers and add / modify them in the database
        foreach($this->answers_content as $myanswer){

            $count = $count + 1;
            // update the answer points if it is empty
            if(empty($myanswer['points'])){
                $myanswer['points'] = 0;
            }

            // validate that every answer has only 1 or 0
            if($myanswer['points'] != 0 && $myanswer['points'] != 1){
                throw new ValidationException(['multiplechoice_single' => 'Een antwoord moet ofwel 1 (true) of 0 (false) punten hebben']);
            }

            // make a check that for a single answer the total points can only be 1
            if($this->answer_type == 'multiplechoice_single'){

                if($myanswer['points'] == 1){
                    if(!$hasavalidanswer){ $hasavalidanswer = true; }
                    else { 
                        //throw new ValidationException(['multiplechoice_single_non_unique' => 'Er mag maar 1 antwoord zijn die 1 punt heeft bij een multiplechoice single']);
                    }
                }
            }

            // in case the index is larger than the number of answers already stored, create a new entry and save it
            // + 1 to compensate that index of first element is 0
            if($count > $nrofanswersstored){
                $newanswer = new Answers;
                $newanswer->name = $myanswer['name'];
                $newanswer->points = $myanswer['points'];
                $newanswer->question_id = $this->id;
                $newanswer->myorder = $count;
                $newanswer->save();
            }
            else {
                // update the existing answer already
                $oldanswer = $this->answers()->where('myorder', $count)->first();
                $oldanswer->name = $myanswer['name'];
                $oldanswer->points = $myanswer['points'];
                $oldanswer->save();
            }
        }

        // delete all old answers
        $this->answers()->where('myorder', '>', $count)->delete();

        // make a check that for a single answer the total points can only be 1
        if($this->answer_type == 'multiplechoice_single'){

            if(!$hasavalidanswer){
                throw new ValidationException(['multiplechoice_single_no_answer' => 'Er moet minstens 1 antwoord als juist gemarkeerd zijn.']);
            }
        }
    }

    public static function convertOldInputToQuestionBuilder()
    {
        // get all the questions
        $allquestions = Questions::whereNull('name')->get();

        // loop over them and update the question_builder
        foreach($allquestions as $q)
        {
            $q->processQuestionBuilder();
            $q->save();
        }
    }

    public function processQuestionBuilder()
    {

        // loop over the question builder entries
        $qb = $this->question_builder;

        // it cannot be empty
        if(empty($qb))
        {
            throw new ValidationException(['empty_question_builder' => 'De vraag moet geformuleerd zijn.']);
        }

        // count the names
        $namecount = 0;

        // loop over the question builder and check that exactly 1 "name" is given
        foreach($qb as $key => $entry)
        {
            if($entry['format'] == 'name')
            {
                $namecount = $namecount + 1;
                $this->name = $entry['name'];
            }

            // in case entry is empty, remove it
            if(empty($entry[$entry['format']]))
            {
                unset($qb[$key]);
            }
            else {
                // empty the others. This is shortcut for: $entry['textarea'] = [], $entry['editor'] = [] etc.
                $qb[$key] = [
                    'format' => $entry['format'],
                    $entry['format'] => $entry[$entry['format']]
                ];

                // in case it is an image, add back the image_max_width
                if($entry['format'] == 'image')
                {
                    $qb[$key]['image_max_width'] = $entry['image_max_width'];
                    $qb[$key]['image_hidden'] = $entry['image_hidden'];
                }
            }
        }

        // check exactly 1 name is given
        if($namecount == 0 || empty($this->name))
        {
            throw new ValidationException(['empty_question_builder' => 'Er is geen vraag geformuleerd. Er moet een vraag geformuleerd zijn.']);
        }

        if($namecount > 1)
        {
            throw new ValidationException(['empty_question_builder' => 'Er is meer dan 1 "vraag" geformuleerd. Er mag maar 1 vraag geformuleerd zijn, de rest moet bestaan uit textareas, afbeeldingen of editors.']);
        }

        // overwrite the question_builder with qb
        $this->question_builder = $qb;

    }

    public function beforeSave() 
    {

        // in case it is not yet created, use afterSave. However, if it is an update, use beforeSave
        if(empty($this->id)){
            $this->processQuestionBuilder();
            return;
        }

        // if there are no updates on the answers_content, just return since there are no checks to be performed (speeds up)
        if(($this->isDirty(['answers_content', 'answer_type']))){
            $this->coupleTheAnswersInDatabase();
        }

        $this->processQuestionBuilder();

    }

    // after create, couple it the first time
    public function afterCreate() {

        // only execute first time after 
        $this->coupleTheAnswersInDatabase();
        $this->processQuestionBuilder();

    }

}
