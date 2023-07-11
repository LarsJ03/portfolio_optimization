<?php namespace Examify\Exams\Models;

use Model;
use Examify\Exams\Models\Courses as Courses;
use Examify\Exams\Models\Schools as Schools;
use Carbon\Carbon as Carbon;

/**
 * Model
 */
class Classes extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_classes';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    // belongs to
    public $belongsTo = [
        'course' => ['Examify\Exams\Models\Courses'],
        'school' => ['Examify\Exams\Models\Schools'],
    ];

    public $belongsToMany = [
        'users' => [
            'Examify\Exams\Models\Users',
            'table' => 'examify_exams_classes_users',
            'pivot' => 'is_teacher',
            'key'   => 'class_id',
            'otherKey' => 'user_id'
        ],
    ];

    // has many
    public $hasMany = [
        'homework' => ['Examify\Exams\Models\Homework', 'key' => 'class_id']
    ];

    public static function getCurrentYear()
    {
        // check the school year
        $thisyear = date('Y');
        $thismonth = date('m');
        
        return round($thisyear + ($thismonth - 2) / 12); // from July it is next schoolyear
    }

    public function students()
    {
        return $this->belongsToMany('Examify\Exams\Models\Users', 'examify_exams_classes_users', 'class_id', 'user_id')->wherePivot('is_teacher', false)->withTimestamps()->orderBy('surname');
    }

    public function homework()
    {
        return $this->hasMany('Examify\Exams\Models\Homework', 'class_id');
    }

    public function getHomework($direction = +1)
    {
        // default, direction is what is upcoming
        if($direction == 1)
        {
            return $this->homework()->where('deleted', '!=', 1)->where('due_date', '>=', Carbon::now())->orderBy('due_date')->get();
        }
        elseif($direction == -1)
        {
            return $this->homework()->where('deleted', '!=', 1)->where('due_date', '<', Carbon::now())->orderByDesc('due_date')->get(); 
        }
        else {
            die('This is not a valid direction.');
        }
    }

    public function teachers()
    {
        return $this->belongsToMany('Examify\Exams\Models\Users', 'examify_exams_classes_users', 'class_id', 'user_id')->wherePivot('is_teacher', true)->withTimestamps();
    }

    public function hasTeacher($user)
    {
        return $this->hasTeacherID($user->id);
    }

    public function hasTeacherID($id)
    {
        return $this->teachers()->get()->contains('id', $id);
    }

    public function course()
    {
        return $this->belongsTo('Examify\Exams\Models\Courses', 'course_id');
    }

    public function getTeachers()
    {
        return $this->teachers()->get();
    }

    public function areAllStudentsOfThisClass($student_ids)
    {
        $students = $this->getStudents();
        $all_ids = $students->pluck('id')->values()->all();
        $test = array_intersect($student_ids, $all_ids);
        return count($test) == count($student_ids);
    }

    public function getStudents()
    {
        return $this->students()->get();
    }

    public static function schoolyearIsCurrentOrNextYear($year)
    {
        $currentyear = Classes::getCurrentYear();
        return ($year == $currentyear || $year == ($currentyear + 1));
    }

    public function isCurrentOrNextYear()
    {
        $currentyear = Classes::getCurrentYear();
        return ($this->schoolyear == $currentyear || $this->schoolyear == ($currentyear + 1));
    }

    // show the course 
    public function getCourseOptions() {

        $myCourses = Courses::all();

        $resultarray = [];

        // get each course
        foreach($myCourses as $course)
        {

            // get the course and level
            $resultarray[$course->id] = $course->level . ' / ' . $course->name;

        }

        return $resultarray;
    }

    public function removeStudent($ids)
    {
        return $this->addStudent($ids, false);
    }

    public function addStudent($ids, $add = true)
    {
        $user = Users::getUser();
        $school = $this->school;

        if(!$user || (!$user->isAdminForSchool($school->id) && !$user->isTeacherForClass($this->id))) {
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of hebt geen rechten meer voor deze klas.',
            ];
        }

        // loop over the student IDs
        $studentids = $ids;

        if(!is_array($studentids)){
            $studentids = array($studentids);
        }

        foreach($studentids as $studentid)
        {

            if(empty($studentid)){
                return [
                    'valid' => false,
                    'message' => 'Selecteer een leraar.'
                ];
            }

            // get the user
            $student = Users::find($studentid);

            if(!$student->count()){
                return [
                    'valid' => false,
                    'message' => 'Deze leraar staat niet in ons systeem.'
                ];
            }

            if(!$student->isStudentForSchool($school->id, $this->schoolyear))
            {
                return [
                    'valid' => false,
                    'message' => 'Deze gebruiker is geen leerling van deze school.'
                ];
            }

            if($add == true)
            {
                // couple the teacher to this class
                $this->students()->syncWithoutDetaching([$student->id => ['is_teacher' => false]]);
            }
            else {
                $this->students()->detach($student->id);
            }
        }

        return false;

    }

}
