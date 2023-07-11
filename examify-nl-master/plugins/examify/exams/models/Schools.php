<?php namespace Examify\Exams\Models;

use Model;
use \Examify\Exams\Models\Licenses as Licences;
use \Examify\Exams\Models\Courses as Courses;
use \Examify\Exams\Models\Classes as Classes;

/**
 * Model
 */
class Schools extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_schools';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $hasMany = [
      'classes' => ['Examify\Exams\Models\Classes', 'key' => 'school_id']
    ];

    public $belongsToMany = [
      'users' => ['Examify\Exams\Models\Users', 'table' => 'examify_exams_schools_users', 
                    'pivot' => [
                      'is_school_admin', 'is_teacher'
                    ],
                    'key' => 'school_id',
                    'otherKey' => 'user_id'
                  ]
    ];

    public function classes()
    {
      return $this->hasMany('Examify\Exams\Models\Classes', 'school_id');
    }

    public function classesSortedByCourse()
    {
      return $this->classes->sortBy('course_id');
    }

    public function classesGroupedByCourse()
    {
      return $this->classes()->with('course', 'teachers', 'students')->get()->sortBy('course_id')->groupBy('course_id');
    }

    public function getTeachers($year)
    {
      // get the teachers that work at this school
      return $this->users()->wherePivot('is_teacher', true)->wherePivot('schoolyear', $year)->get()->sortBy('name');

    }

    public function getTeachersFromLastYear()
    {
      $year = Classes::getCurrentYear() - 1;
      return $this->getTeachers($year);

    }

    public function getStudents($year)
    {
      // get the students associated to this school
      return $this->users()->wherePivot('is_teacher', false)->wherePivot('schoolyear', $year)->get()->sortBy('name');
    }

    public function getInactiveLicences($year)
    {
        return Licences::where('school_id', $this->id)->where('activated', false)->where('is_teacher', true)->where('schoolyear', $year)->get();
    }

    public function getInactiveStudentLicences($year)
    {
      return Licences::where('school_id', $this->id)->where('activated', false)->where('is_teacher', false)->where('course_id', 0)->where('schoolyear', $year)->get();
    }

    public function getLicensesPerCourse($year)
    {
      // get all the courses
      $courses = Courses::orderBy('name')->get();

      // loop over the courses and couple the number of licenses for this school
      foreach($courses as $course)
      {
        $result[] = [
          'course' => $course,
          'count' => Licences::where('school_id', $this->id)->where('activated', true)->where('course_id', $course->id)->where('schoolyear', $year)->count()
        ];
      }

      return $result;

    }
}
