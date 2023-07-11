<?php namespace Examify\Exams\Models;

use Model;
use Examify\Exams\Models\Classes as Classes;
use Examify\Exams\Models\Courses as Courses;
use Redirect;

/**
 * Model
 */
class Licenses extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_licenses';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'class' => 'Examify\Exams\Models\Classes',
        'course' => 'Examify\Exams\Models\Courses',
        'school' => 'Examify\Exams\Models\Schools'
    ];

    public function course()
    {
        return $this->belongsTo('Examify\Exams\Models\Courses', 'course_id');
    }

    public function class()
    {
        return $this->belongsTo('Examify\Exams\Models\Classes', 'class_id');
    }

    public function school()
    {
        return $this->belongsTo('Examify\Exams\Models\Schools', 'school_id');
    }


    public function getClassOptions()
    {

        $myClasses = Classes::all();

        $resultarray = [];

        foreach($myClasses as $class)
        {

            // get the course name, level and classname
            $resultarray[$class->id] = $class->course->level . ' / ' . $class->course->name . ' / ' . $class->school->name . ' / ' . $class->name;

        }

        return $resultarray;

    }

    public function getCourseOptions()
    {
        $myCourses = Courses::all();

        $resultarray = [];
        foreach($myCourses as $course)
        {
            $resultarray[$course->id] = $course->name . ' [' . $course->level . ']';
        }

        return $resultarray;
    }

    // generate the key
    public function generateKey()
    {
        $this->key = bin2hex(openssl_random_pseudo_bytes(32));
    }

        // overwrite the save method to generate the tokens
    public function beforeCreate()
    {

        // check if the license is defined, if not it is not from a user form but it is the recursive called function
        if(!isset($this->nlicensestogenerate)){
            return;
        }

        // check how many licenses need to be generated
        $nlicences = intval($this->nlicensestogenerate);

        // in case it is 0
        if($nlicences < 1){
            throw new \Exception("The number of licenses should be larger than 0. (now is " . $nlicences . ")");
        }

        // get the course
        $myCourse = $this->course;

        // generate them
        for($ii = 0; $ii < $nlicences - 1; $ii++)
        {

            // generate a licence for this class
            $newLicence = new \Examify\Exams\Models\Licenses();
            $newLicence->course_id = $myCourse->id;
            $newLicence->key = bin2hex(openssl_random_pseudo_bytes(32));
            $newLicence->school_id = 0;
            $newLicence->schoolyear = Classes::getCurrentYear();
            $newLicence->save();

        }

        // also do it for this license
        $this->course_id = $myCourse->id;
        $this->school_id = 0;
        $this->schoolyear = Classes::getCurrentYear();
        $this->key = bin2hex(openssl_random_pseudo_bytes(32));

        unset($this->attributes['nlicensestogenerate']);

        //throw new \Exception($nlicences . " licence(s) have been successfully generated.");

    }
}
