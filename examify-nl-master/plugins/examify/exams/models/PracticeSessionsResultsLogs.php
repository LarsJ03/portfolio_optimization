<?php namespace Examify\Exams\Models;

use Model;

/**
 * Model
 */
class PracticeSessionsResultsLogs extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_practice_sessions_results_logs';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function questionType()
    {
        return $this->belongsTo('Examify\Exams\Models\QuestionTypes', 'question_type_id');
    }

    public $belongsTo = [
        'questionType' => ['Examify\Exams\Models\QuestionTypes', 'question_type_id'],
        'question' => ['Examify\Exams\Models\Questions', 'question_id']
    ];
}
