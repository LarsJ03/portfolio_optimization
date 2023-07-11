<?php namespace Examify\Exams\Models;

use Model;
use \Examify\Exams\Models\Texts as Texts;
use \Examify\Exams\Models\Schools as Schools;
use \Examify\Exams\Models\Classes as Classes;
use \Examify\Exams\Models\Users as Users;
use \Examify\Exams\Models\QuestionsAnswersLogs as QAL;
use \Examify\Exams\Models\PracticeSessionsResultsLogs as PSRL;
use Carbon\Carbon as Carbon;

/**
 * Model
 */
class Homework extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_homework';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    // has many practice session ids
    public $hasMany = [
        'practiceSessions' => ['Examify\Exams\Models\PracticeSessions', 'key' => 'homework_id' ],
    ];

    public $belongsToMany = [
        'users' => [
            'Examify\Exams\Models\Users', 
            'table' => 'examify_exams_practice_sessions',
            'key' => 'homework_id',
            'otherKey' => 'user_id'
        ]
    ];

    public $jsonable = [
        'questions',
        'texts',
        'child_ids'
    ];

    public $belongsTo = [
        'class' => ['Examify\Exams\Models\Classes', 'key' => 'class_id'],
        'questionTypesTrial' => ['Examify\Exams\Models\QuestionTypesTrials', 'key' => 'question_types_trials_id']
    ];

    public function class()
    {
        return $this->belongsTo('Examify\Exams\Models\Classes', 'class_id');
    }

    public function questionTypesTrial()
    {
        return $this->belongsTo('Examify\Exams\Models\QuestionTypesTrials', 'question_types_trials_id');
    }

    public function getClass()
    {
        return $this->class()->with('course')->first();
    }

    public function isOverDueDate()
    {
        return $this->due_date < Carbon::now();
    }

    public function getTexts()
    {
        $ids = collect($this->texts);
        return Texts::with('exam')->find($ids)->sortBy('myorder')->sortBy('exam_id')->sortBy('exam.tijdvak')->sortByDesc('exam.year');
    }

    public function getTextsGroupedByExam()
    {
        $texts = $this->getTexts();
        return $texts->groupBy('exam_id');
    }

    public function practiceSessions()
    {
        return $this->hasMany('Examify\Exams\Models\PracticeSessions', 'homework_id');
    }

    public function getSiblings()
    {
        if(!$this->parent_id){ return collect([]); }
        $parent = Homework::find($this->parent_id);

        // get the children 
        return $parent->getChildren();
    }

    public function getPracticeSessions()
    {
        // in case it has children, generate the practicesessions of them as well
        return $this->practiceSessions()->get();
        

        $children = $this->getChildren();
        $result = collect([]);
        foreach($children as $child)
        {
            $result = $result->merge($child->getPracticeSessions());
        }
        return $result;
    }

    public function getSessionForUserID($userid)
    {
        return $this->practiceSessions()->where('user_id', $userid)->first();
    }

    public function getQuestionTypesTrial()
    {
        return $this->questionTypesTrial()->first();
    }

    public function getQuestions()
    {
        if(!$this->split_exam){
            return $this->questions;
        }

        // it is a split exam, the questions are related to its children
        $children = $this->getChildren();
        $n = $children->count();
        if(!$n > 0){
            return collect([]);
        }

        if($n == 1){
            return $children->first()->questions;
        }
        
        return $children->pluck('questions')->flatten(1);
    }

    public function hasChildren()
    {
        if($this->getChildren()->count()){
            return true;
        }

        return false;
    }

    public function getChildren($desc=true)
    {
        $children = Homework::find($this->child_ids);
        if(!$children){ return collect([]); }

        if($desc)
        {
            return $children->where('deleted', false)->sortByDesc('due_date');
        }
        else {
            return $children->where('deleted', false)->sortBy('due_date');
        }
    }

    public function getPracticeSessionsWithStudentNameAndProgress()
    {
        // include the student name in the session
        $ps = $this->practiceSessions()->with('user')->where('deleted', false)->get();

        if(!$ps->count()){
            return $ps;
        }

        // update them with the name of the student
        foreach($ps as $myps)
        {
            // check if the name can be extracted
            if(!empty($myps->user))
            {
                $myps->studentname = $myps->user->name . ' ' . $myps->user->surname;
                $myps->surname = $myps->user->surname;
                $myps->email = $myps->user->email;
            }
            else {
                $myps->studentname = 'Verwijderd';
                $myps->surname = '';
                $myps->email = 'Verwijderd';
            }

            $myps->percentagecompleted = $myps->getPercentageCompleted(false);
            $myps->percentageuncompleted = 100 - $myps->percentagecompleted;
            $myps->nquestions = $this->nquestions;
            $myps->nansweredquestions = round($myps->nquestions * $myps->percentagecompleted / 100);
            $myps->nunansweredquestions = $myps->nquestions - $myps->nansweredquestions;
        }

        return $ps->sortBy('surname');
    }

    public function getPracticeSessionsWithStudentNameAndPoints()
    {
        // include the student name in the session
        $ps = $this->practiceSessions()->where('deleted', false)->get();

        if(!$ps->count()){
            return $ps;
        }

        // update them with the name of the student
        foreach($ps as $myps)
        {
            // check if the name can be extracted
            if(!empty($myps->user))
            {
                $myps->studentname = $myps->user->name . ' ' . $myps->user->surname;
                $myps->surname = $myps->user->surname;
                $myps->email = $myps->user->email;
            }
            else {
                $myps->studentname = 'Verwijderd';
                $myps->surname = '';
                $myps->email = 'Verwijderd';
            }
            
            $myps->nquestions = $this->nquestions;

            // sum it
            $myps->pointsAchieved      = $myps->totalpointsachieved;
            $myps->pointsUnanswered    = $myps->totalpointsunanswered;
            $myps->pointsWrongAnswered         = $myps->totalpointsavailable - $myps->totalpointsachieved - $myps->totalpointsunanswered;
        }

        return $ps->sortBy('surname');
    }


    public function cloneForDemoSchool()
    {

        $year = Classes::getCurrentYear();


        // get the DemoSchool
        $demoschool = Schools::where('name', 'DemoSchool')->first();

        // get the class, and check if the same kind of class exists within demo
        $class = $this->class;
        $democlass = Classes::where('school_id', $demoschool->id)
               ->where('name', $class->name)
               ->where('course_id', $class->course_id)
               ->where('schoolyear', $year)
               ->first();

        if(!$democlass){

            // create a democlass based on the class
            $democlass = $class->replicate();
            $democlass->school_id = $demoschool->id;
            $democlass->schoolyear = $year;
            $democlass->save();

        }

        // duplicate the homework item, only if the name is not there yet
        $hw = Homework::where('school_id', $demoschool->id)
                        ->where('class_id', $democlass->id)
                        ->where('name', $this->name)->first();

        if(!$hw){ 

            $hw = $this->replicate();
            $hw->school_id = $demoschool->id;
            $hw->class_id = $democlass->id;
            $hw->save();

        }

        else {

            // delete all the current corresponding practice sessions
            $pss = $hw->getPracticeSessions();
            foreach($pss as $ps){
                $ps->delete();
            }
        }


        $pss = $this->getPracticeSessions();

        // couple students to this class
        $students = $class->getStudents();
        $count = 0;
        foreach($students as $student)
        {
            $count++;
            // couple them
            $email = sprintf('leerling%03d@examify.nl', $count);

            $student = Users::where('email', $email)->first();
            if(!$student){
                $student = new Users();
                $student->name = 'Leer';
                $student->surname = sprintf('Ling%03d', $count);
                $student->email = $email;
                $student->generatePassword();
                $student->is_activated = true;
                $student->username = $email;
                $student->save();
            }

            $student->makeStudentForSchool($demoschool->id, $democlass->schoolyear, true, false);

            $democlass->addStudent($student->id);

            // pick the first practice session and couple it to this user
            $ps = $pss[$count - 1];
            $demops = $ps->replicate();
            $demops->user_id = $student->id;
            $demops->homework_id = $hw->id;
            $demops->created_at = $ps->created_at;
            $demops->updated_at = $ps->updated_at;
            $demops->save();

            // replicate also all the question answer logs
            $ca = collect($ps->cached_answers);
            if($ca->count()){
                foreach($ca as $key => $i)
                {
                    if(!is_array($i)){ continue; }
                    if($i['log_id'] == 0){ 
                        continue;
                    }
                    $log = QAL::find($i['log_id']);
                    if(!$log){ continue; }
                    $demolog = $log->replicate();
                    $demolog->user_id = $student->id;
                    $demolog->practice_session_id = $demops->id;
                    $demolog->created_at = $log->created_at;
                    $demolog->updated_at = $log->updated_at;
                    $demolog->save();
                }
            }

            $cp = collect($ps->cached_points);
            if($cp->count()){
                foreach($cp as $key => $i)
                {
                    if(!is_array($i)){ continue; }
                    if($i['log_id'] == 0){ continue; }
                    $log = PSRL::find($i['log_id']);
                    if(!$log){ continue; }
                    $demolog = $log->replicate();
                    $demolog->user_id = $student->id;
                    $demolog->practice_session_id = $demops->id;
                    $demolog->created_at = $log->created_at;
                    $demolog->updated_at = $log->updated_at;
                    $demolog->save();
                }
            }

            // generate the cache again for this ps
            $demops->generateCachedAnswersProperty();
            $demops->generateCachedPointsProperty();

        }

    

        dd($democlass);


    }

}
