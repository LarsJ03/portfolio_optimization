<?php namespace Examify\Exams\Models;

use Model;

/**
 * Model
 */
class QuestionTypesExplanations extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_question_types_explanations';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    // the relationships
    public $belongsTo = [
        'question_type' => ['Examify\Exams\Models\QuestionTypes'],
        'course'        => ['Examify\Exams\Models\Courses']
    ];
}
