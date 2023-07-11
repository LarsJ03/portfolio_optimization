<?php namespace Examify\Exams\Models;

use Model;
use Examify\Exams\Models\Questions as Questions;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\PracticeSessions as PS;

/**
 * Model
 */
class QuestionsAnswersLogs extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_questions_answers_logs';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'practiceSession' => ['Examify\Exams\Models\PracticeSessions', 'key' => 'practice_session_id'],
        'user' => ['Examify\Exams\Models\Users', 'key' => 'user_id'],
        'question' => ['Examify\Exams\Models\Questions', 'key' => 'question_id'],
    ];

    // save the cached answer as the final one
    public static function saveCachedAnswer(Questions $question, $practice_session_id)
    {

        if(!$user = Users::getUser()){
            return;
        }
        
        $user_id = $user->id;
        $ps = PS::find($practice_session_id);

        if(!$ps->isVisibleForUser($user)){ 
            return;
        }

        $ca = collect($ps->cached_answers)->sortByDesc('log_id');

        // get the answer type 
        if($question->answer_type == 'open' || $question->answer_type == 'multiplechoice_single' || $question->answer_type == 'multiplechoice_multiple')
        {
            $myAnswer = $ca->firstWhere('question_id', $question->id);
            if(!$myAnswer){ return; }

            // get the cached answer
            $mylog = QuestionsAnswersLogs::find($myAnswer['log_id']);
            if(!$mylog){ return; }

            $mylog->is_final = true;
            $mylog->save();
            return;
        }
        elseif($question->answer_type == 'truefalse')
        {
            // loop over all the entries and see if there is something cached
            $myAnswers = $ca->where('question_id', $question->id);
            if(!$myAnswers){ return; }

            // get the answered statements
            $last_answers = $myAnswers->unique('answer_id');

            foreach($last_answers as $answer){
                $mylog = QuestionsAnswersLogs::find($answer['log_id']);
                if(!$mylog){ continue; }

                $mylog->is_final = true;
                $mylog->save();
            }

            return;
        }
    }

    public static function getCachedAnswer(Questions $question, $practice_session_id)
    {
        // TBD, check the user
        if(!$user = Users::getUser()){
            return;
        }
        
        $user_id = $user->id;
        $ps = PS::find($practice_session_id);

        if(!$ps->isVisibleForUser($user)){ 
            return;
        }

        $ca = collect($ps->cached_answers);

        // get the answer type 
        if($question->answer_type == 'open')
        {
            $myAnswer = $ca->firstWhere('question_id', $question->id);
            if(!$myAnswer){ return; }

            return [
                'value' => $myAnswer['open_answer'],
                'points' => $myAnswer['points'],
                'logobject' => QuestionsAnswersLogs::find($myAnswer['log_id'])
            ];
        }
        elseif($question->answer_type == 'multiplechoice_single' || $question->answer_type == 'multiplechoice_multiple')
        {   
            $myAnswer = $ca->firstWhere('question_id', $question->id);
            if(!$myAnswer){ return; }

            // return just the answer id
            return $myAnswer['answer_id'];
        }
        elseif($question->answer_type == 'truefalse')
        {
            $myAnswers = $ca->where('question_id', $question->id);
            if(!$myAnswers){ return []; }

            // get the answered statements
            $last_answers = $myAnswers->unique('answer_id');
            $points = $last_answers->pluck('points')->values()->all();
            $answer_ids = $last_answers->pluck('answer_id')->values()->all();

            return array_combine($answer_ids, $points);

        }

    }
}
