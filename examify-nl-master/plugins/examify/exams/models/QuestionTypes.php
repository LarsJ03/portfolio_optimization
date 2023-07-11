<?php namespace Examify\Exams\Models;

use Model;

/**
 * Model
 */
class QuestionTypes extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_question_types';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $hasMany = [
        'exams' => ['Examify\Exams\Models\Exams', 'key' => 'type_id'],
        'explanations' => ['Examify\Exams\Models\QuestionTypesExplanations', 'key' => 'question_type_id']
    ];
}
