<?php namespace Examify\Exams\Models;

use Model;

/**
 * Model
 */
class UsersSubscriptions extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_users_subscriptions';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function course(){
        return $this->belongsTo('Examify\Exams\Models\Courses', 'course_id');
    }

    public $belongsTo = [
        'user' => ['Examify\Exams\Models\Users'],
        'course' => ['Examify\Exams\Models\Courses']
    ];
}
