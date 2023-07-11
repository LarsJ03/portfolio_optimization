<?php namespace Examify\Exams\Models;

use Model;

/**
 * Model
 */
class UsersSettings extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_users_settings';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $fillable = ['user_id', 'field'];
}
