<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use Examify\Exams\Models\Questions as Questions;
use Examify\Exams\Models\QuestionsAnswersLogs as QuestionsAnswersLogs;

use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\PracticeSessions as PracticeSessions;
use Carbon\Carbon as Carbon;

use DB;

class Question extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Question Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'question_id' => [
                'title'         => 'Question ID',
                'description'   => 'Question ID',
                'default'       => 0,
                'type'          => 'string',
                'validationPattern'     => '^[0-9]+$',
                'validationMessage'     => 'Question ID should be a numeric value'
            ],
            'practice_session_id' => [
                'title'         => 'Practice Session ID',
                'description'   => 'Practice Session ID',
                'default'       => 0,
                'type'          => 'string',
                'validationPattern'     => '^[0-9]+$',
                'validationMessage'     => 'Practice Session ID should be a numeric value'
            ],
            'is_finished' => [
                'title'         => 'Is finished',
                'description'   => '[1] if the layout should be that the question is finished by the user already. [0] if not',
                'default'       => 0,
                'type'          => 'string',
                'validationPattern'     => '^[0-9]+$',
                'validationMessage'     => 'It should be a numeric value'
            ],
            'to_be_checked' => [
                'title'         => 'To be checked',
                'description'   => '[1] To show the answer correction model. [0] if not',
                'default'       => 0,
                'type'          => 'string',
                'validationPattern'     => '^[0-9]+$',
                'validationMessage'     => 'It should be a numeric value'
            ],
        ];
    }



    public function onRender()
    {
        // get the question ID
        $QID    = $this->property('question_id');

        // get the practice session ID
        $this->page['psid']   = $psid = $this->property('practice_session_id');

        // get the question model and set it as variable
        $this->page['question'] = $myquestion = Questions::find($QID);

        if(!$myquestion)
        {
            exit;
        }

        // in case the psid=0, it is called to just show the question
        if($psid != 0)
        {
            // based on the answer type, get the answer given by the user
            $this->page['cached_answer'] = QuestionsAnswersLogs::getCachedAnswer($myquestion, $psid);
        }

        // check if it should be disabled or not
        if(!$thisSession = PracticeSessions::find($psid))
        {
            $thisIsFinished = false;
            $randomizeAnswers = false;
        }
        else {
            $thisIsFinished = false;
            $randomizeAnswers = false;
            
            $thisIsFinished = $thisSession->finished || ($thisSession->leermodus && $myquestion->isLocked($psid));

            // get the question with the id
            $this->page['pointsScored'] = $thisSession->getPointsScored($QID);

            // get the leermodus
            $this->page['leermodus'] = $thisSession->leermodus;

            $randomizeAnswers = $thisSession->homework_id && !$thisSession->finished;

        }

        $this->page['randomizeAnswers'] = $randomizeAnswers;

        // let it be overloaded by the finished parameter        
        $this->page['practice_session_is_finished'] = ($this->property('is_finished') || $thisIsFinished);

        // store whether it needs to be checked still (in open questions)
        $this->page['to_be_checked'] = $this->property('to_be_checked');

        // show the corrections in case PSID is 0
        if($psid == 0)
        {
            $this->page['show_corrections'] = true;
        }


    }

    public function isValidQuestionIDinput()
    {
        $PSID = input('psid');
        $QID = input('qid', 0);

        if($QID == 0)
        {
            return false;
        }

        // check if this question indeed belongs to this practice session
        $practicesession = PracticeSessions::find($PSID);

        if(!$practicesession){ return false; }

        // check if the practice session itself has no time limit. And if so, check if it is not exceeded yet.
        if($practicesession->isExpired()){ return false; }

        return $practicesession->hasQuestionID($QID);
    }

    public function onSubmitAnswerMultipleChoiceSingle()
    {

        $PSID = input('psid');

        //TBD: validate that the practice session ID is from this user
        // get the user
        if(!($user = Users::getUser()) || !($user->hasActivePracticeSession($PSID))){
            return;
        }

        if(!$this->isValidQuestionIDinput())
        {
            return;
        }

        // check if this question answer combination exists
        $answer = DB::table('examify_exams_answers')->where('question_id', input('qid'))->where('id', input('aid'))->get();

        if(!$answer->count())
        {
            return;
        }

        $qid = input('qid');
        $aid = input('aid');

        // get the answer that is selected and add it to the logs
        $mylog = new QuestionsAnswersLogs();
        $mylog->user_id = $user->id; // to be updated
        $mylog->question_id         = $qid;
        $mylog->practice_session_id = $PSID;
        $mylog->answer_id           = $aid;
        $mylog->points              = 1;
        $mylog->save();

        // update the cached answer of the practice session
        $ps = PracticeSessions::find($PSID);
        $cached_answers = collect($ps->cached_answers);

        $cached_answers->prepend(['question_id' => $qid, 'answer_id' => $aid, 'points' => 1, 'log_id' => $mylog->id]);

        // make sure the one with the highest log_id is the first in the collection - since the highest log id means last added answer.
        $ps->cached_answers = $cached_answers->sortByDesc('log_id')->values()->all(); 
        $ps->save();

    }

    public function onSubmitAnswerTrueFalse()
    {

        $PSID = input('psid');

        //TBD: validate that the practice session ID is from this user
        // get the user
        if(!($user = Users::getUser()) || !($user->hasActivePracticeSession($PSID))){
            return;
        }

        // in case it is locked, do not answer
        if(!$this->isValidQuestionIDinput())
        {
            return;
        }

        // check if this question answer combination exists
        $answer = DB::table('examify_exams_answers')->where('question_id', input('qid'))->where('id', input('aid'))->get();
        
        if(!$answer->count())
        {
            return;
        }

        $qid = input('qid');
        $aid = input('aid');
        $pts = input('points');

        // get the answer that is selected and add it to the logs
        $mylog = new QuestionsAnswersLogs();
        $mylog->user_id = $user->id; // to be updated
        $mylog->question_id         = $qid;
        $mylog->practice_session_id = $PSID;
        $mylog->answer_id           = $aid;
        $mylog->points              = $pts;
        $mylog->save();

        // update the cached answer of the practice session
        $ps = PracticeSessions::find($PSID);
        $cached_answers = collect($ps->cached_answers);

        $cached_answers->prepend(['question_id' => $qid, 'answer_id' => $aid, 'points' => $pts, 'log_id' => $mylog->id]);

        // make sure the one with the highest log_id is the first in the collection - since the highest log id means last added answer.
        $ps->cached_answers = $cached_answers->sortByDesc('log_id')->values()->all(); 
        $ps->save();

    }

    public function onSubmitAnswerOpenQuestion()
    {

        $PSID = input('psid');

        //TBD: validate that the practice session ID is from this user
        // get the user
        if(!($user = Users::getUser()) || !($user->hasActivePracticeSession($PSID))){
            return;
        }

        // in case it is locked, do not answer
        if(!$this->isValidQuestionIDinput())
        {
            return;
        }

        // get the question id, practice session id
        $qid    = input('qid');
        $psid   = input('psid');
        $open_answer = input('open_answer');

        // TBD: validate that the practice session ID is from this user

        // store the answer
        $mylog = new QuestionsAnswersLogs();
        $mylog->user_id = $user->id; // to be updated
        $mylog->question_id         = $qid;
        $mylog->practice_session_id = $PSID;
        $mylog->open_answer         = $open_answer;
        $mylog->save();

        // update the cached answer of the practice session
        $ps = PracticeSessions::find($PSID);
        $cached_answers = collect($ps->cached_answers);

        $cached_answers->prepend(['question_id' => $qid, 'open_answer' => $open_answer, 'points' => null, 'log_id' => $mylog->id]);

        // make sure the one with the highest log_id is the first in the collection - since the highest log id means last added answer.
        $ps->cached_answers = $cached_answers->sortByDesc('log_id')->values()->all(); 
        $ps->save();

    }
}
