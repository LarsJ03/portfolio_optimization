<?php namespace Examify\Exams\Models;

use Model;

/**
 * Model
 */
class Answers extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_answers';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    // belongs to a question
    public $belongsTo = [
        'question' => 'Examify\Exams\Models\Questions'
    ];

}
