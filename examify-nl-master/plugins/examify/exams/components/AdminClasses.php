<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use Examify\Exams\Models\Schools as Schools;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\Courses as Courses;
use Examify\Exams\Models\Classes as Classes;
use DB;

class AdminClasses extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AdminClasses Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'school_id' => [
                'title'         => 'School ID',
                'description'   => 'School ID',
                'default'       => 0,
                'type'          => 'string',
                'validationPattern'     => '^[0-9]+$',
                'validationMessage'     => 'School ID should be a numeric value'
            ]
        ];


    }


    function onRender()
    {

        $user = Users::getUser();

        // get the year
        $this->page['schoolyear'] = $schoolyear = $this->param('year');

        $school = Schools::find($this->property('school_id'));

        if(!$school->count()){
            return;
        }

        $isSchoolAdmin = $user->isAdminForSchool($this->property('school_id'));
        if(!$isSchoolAdmin && !$user->isTeacherForSchool($this->property('school_id'), $schoolyear))
        {
            return;
        }

        // set the school
        $this->page['school'] = $school;

        $this->page['school_id'] = $school->id;

        $currentyear = Classes::getCurrentYear();

        $this->page['currentyear'] = $currentyear;

        $this->page['courses'] = Courses::all();

        // store if the user is an admin (for adding purposes)
        $this->page['isSchoolAdmin'] = $isSchoolAdmin;

    }

    function onAdd()
    {
        // get the information 
        $classname = input('classname');
        $schoolyear = $this->param('year');
        $courseid = input('course_id');
        $schoolid = input('school_id');

        // validate that the user is admin of this school
        $user = Users::getUser();

        if(!$user || !$user->isAdminForSchool($schoolid)){
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of hebt geen rechten meer voor deze school.',
            ];
        }

        // validate the classname and schoolyear are valid
        if(empty($classname)){
            return [
                'valid' => false,
                'message' => 'De klasnaam moet ingevuld worden.'
            ];
        }

        if(empty($schoolyear)){
            return [
                'valid' => false,
                'message' => 'Kies een schooljaar.'
            ];
        }

        $schoolyear = intval($schoolyear);
        if($schoolyear < 2019){
            return [
                'valid' => false,
                'message' => 'Kies een geldig schooljaar.'
            ];
        }

        if(empty($courseid)){
            return [
                'valid' => false,
                'message' => 'Selecteer een vak.'
            ];
        }

        // check if the course id exists
        $check = Courses::find($courseid)->count();
        if($check == 0){
            return [ 
                'valid' => false,
                'message' => 'Selecteer een geldig vak.'
            ];
        }

        // create the class
        $myClass = new Classes();
        $myClass->school_id = $schoolid;
        $myClass->course_id = $courseid;
        $myClass->name = $classname;
        $myClass->schoolyear = $schoolyear;
        $myClass->save();

        // get the school
        $school = Schools::find($schoolid);

        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-classes-' . $schoolid => $this->renderPartial('examifyHelpers/portal/listOfClasses', 
                        [
                            'school_id' => $schoolid,
                            'school' => $school,
                            'schoolyear' => $schoolyear,
                            'courses' => Courses::all()
                        ])
            ]
        ];

    }

}
