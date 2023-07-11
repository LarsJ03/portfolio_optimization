<?php namespace Examify\Exams\Models;

use Model;

/**
 * Model
 */
class QuestionsLocks extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_practice_session_question_locked';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
