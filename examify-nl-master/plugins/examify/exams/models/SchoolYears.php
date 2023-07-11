<?php namespace Examify\Exams\Models;

use Model;

/**
 * Model
 */
class SchoolYears extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_schoolyears';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];
}
