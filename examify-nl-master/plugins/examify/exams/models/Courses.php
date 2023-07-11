<?php namespace Examify\Exams\Models;

use Model;
use \Examify\Exams\Models\Users as Users;
use \Examify\Exams\Models\Classes as Classes;
use \Examify\Exams\Models\QuestionTypes as QuestionTypes;
use \Examify\Exams\Models\PracticeSessions as PracticeSessions;
use DB;

/**
 * Model
 */
class Courses extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_courses';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function classes()
    {
        return $this->hasMany('Examify\Exams\Models\Classes', 'course_id');
    }

    public function exams()
    {
        return $this->hasMany('Examify\Exams\Models\Exams', 'course_id')->where('visible', true);
    }

    public function questionTypesTrials()
    {
        return $this->hasMany('Examify\Exams\Models\QuestionTypesTrials', 'course_id')->where('visible', true);
    }

    public function getExams()
    {
        return $this->exams()->get()->sortBy('tijdvak')->sortByDesc('year')->values();
    }

    public function getQuestionTypesTrials()
    {
        return $this->questionTypesTrials()->orderBy('sort_order')->get();
    }

    // get the exam ids order

    public function getQuestionTypesTrialsGroupedByType()
    {
        $trials = $this->getQuestionTypesTrials();
        if(!$trials->count()){ return collect([]); }

        return $trials->groupBy('question_type_id');

        // also return the 
    }

    public function getQuestionTypeById($id)
    {
        return QuestionTypes::find($id);
    }

    public function getPracticeSessionsForUser($user = [])
    {
        if(empty($user)){ 
            $user = Users::getUser();
        }   

        if(!$user){ return; }

        return PracticeSessions::where('course_id', $this->id)->where('user_id', $user->id)->get();
    }

    public function getExamsWithPracticeModeAvailable($PMA = false)
    {
        if(!$PMA){
            return $this->getExams();
        }

        return $this->exams()->where('practice_mode_available', $PMA)->get()->sortBy('tijdvak')->sortByDesc('year')->values();
    }

    public static function getFlagUrlForCourseName($coursename)
    {
        // convert the coursename to flags
        switch ($coursename) {
            case 'Engels':
                $filename = 'united-kingdom.svg';
                break;

            case 'Nederlands':
                $filename = 'netherlands.svg';
                break;

            case 'Duits':
                $filename = 'german.svg';
                break;

            case 'Frans':
                $filename = 'france.svg';
                break;

            case 'Spaans':
                $filename = 'spain.svg';
                break;

            
            default:
                $filename = 'error.svg';
                # code...
                break;
        }

        return $filename;
    }

    public function getFlagUrl()
    {
        return $this->getFlagUrlForCourseName($this->name);

    }

    // get the other levels
    public function getOtherLevels()
    {
        return Courses::where('name', $this->name)->where('id', '!=', $this->id)->get()->pluck('level')->values()->all();
    }

    public function getAllLevels()
    {
        // set level in front of the array
        $arr = $this->getOtherLevels();
        array_unshift($arr, $this->level);
        return $arr;
    }

    public function getExamsForLevel($level = '')
    {
        return $this->getExamsForLevelWithPracticeModeAvailable($level);
    }

    public function getExamsForLevelWithPracticeModeAvailable($level = '', $PMA = false)
    {
        // get all the exams for the course of the level given
        if(empty($level) || $level == $this->level)
        {
            return $this->getExams();
        }

        // find the course with the same name and level
        $course = Courses::where('name', $this->name)->where('level', $level)->first();

        if(!$course)
        {
            return;
        }

        return $course->getExamsWithPracticeModeAvailable($PMA);
    }

    public function getQuestionTypesTrialsForLevel($level = '')
    {
        // get all the exams for the course of the level given
        if(empty($level) || $level == $this->level)
        {
            return $this->getQuestionTypesTrials();
        }

        // find the course with the same name and level
        $course = Courses::where('name', $this->name)->where('level', $level)->first();

        if(!$course)
        {
            return collect([]);
        }

        return $course->getQuestionTypesTrials();
    }

    public function getQuestionTypesTrialsGroupedByTypeForLevel($level = '')
    {
        $trials = $this->getQuestionTypesTrialsForLevel($level);

        if(!$trials->count()){
            return collect([]);
        }

        // group them by question type id
        $grouped = $trials->groupBy('question_type_id');
        $types = QuestionTypes::find($trials->pluck('question_type_id')->unique()->values()->all());
        $types = $types->sortBy('name');
        return [
            'types' => $types,
            'trials' => $grouped
        ];
    }

    public function getCourseLevelAttribute()
    {
        return $this->level . ' / ' . $this->name;
    }

    public function getCourseAndLevelAttribute()
    {
        return $this->name . ' [' . $this->level . ']';
    }

    public function examsWithPracticeModeAvailable($PMA)
    {
        return $this->exams()->where('practice_mode_available', $PMA);
    }

    public function hasExamsWithPracticeModeAvailable($PMA)
    {
        return $this->examsWithPracticeModeAvailable($PMA)->limit(1)->get()->count() > 0;
    }

    public $hasMany = [
        'exams' => ['Examify\Exams\Models\Exams', 'key' => 'course_id']
    ];

    // count the students in the classes
    public function getCountStudentsInClasses($school, $year)
    {
        // the user should be logged in 
        $user = Users::getUser();

        if(!$user || !$school || !$user->isSuperAdmin())
        {
            return collect([]);
        }

        // get the classes for this school and this year and this course
        $allclasses = $this->classes()->where('school_id', $school->id)->where('schoolyear', $year)->get();

        // get the class ids
        $ids = $allclasses->pluck('id')->values();

        // get the count of the classes ids
        return DB::table('examify_exams_classes_users')->whereIn('class_id', $ids)->where('is_teacher', 0)->count();


    }

    public function getClassesForSchool($school, $year)
    {

        // the user should be logged in
        $user = Users::getUser();

        if(!$user)
        {
            return collect([]);
        }

        // check if school is not empty
        if(!$school)
        {
            return collect([]);
        }

        // get all the classes for this course and year
        $allclasses = $this->classes()->where('school_id', $school->id)->where('schoolyear', $year)->get();

        // in case no classes are there, return
        if(!$allclasses->count())
        {
            return collect([]);
        }

        // in case the user is the admin for this school, show all
        if($user->isAdminForSchool($school))
        {
            // get the classes for this school
            return $allclasses;
        }

        // only get the classes for which the user is a teacher
        foreach($allclasses as $key => $class)
        {
            if(!$user->isTeacherForClass($class->id))
            {
                $allclasses->forget($key);
            }
        }

        return $allclasses;


    }

}
