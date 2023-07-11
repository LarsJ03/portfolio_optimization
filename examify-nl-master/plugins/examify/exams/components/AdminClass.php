<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\Classes as Classes;

class AdminClass extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AdminClass Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'class_id' => [
                'title'         => 'Class ID',
                'description'   => 'Class ID',
                'default'       => 0,
                'type'          => 'string',
                'validationPattern'     => '^[0-9]+$',
                'validationMessage'     => 'Class ID should be a numeric value'
            ]
        ];
    }

    function onRender()
    {

        $this->page['user'] = $user = Users::getUser();

        // check if the user administrates this class
        if(!$user){
            return;
        }

        // get the class
        $class = Classes::find($this->property('class_id'));

        if(!$class)
        {
          return;
        }

        // validate the user is a teacher
        if(!$user->isAdminForSchool($class->school_id) && !$user->isTeacherForClass($class->id))
        {
            return;
        }

        $this->page['class'] = $class;
        $this->page['school'] = $class->school;
        $this->page['schoolyear'] = $this->param('year');
        $this->page['currentyear'] = Classes::getCurrentYear();
        $this->page['form_enabled'] = (Classes::getCurrentYear() <= $this->param('year'));

    }

    function onRemoveTeacher()
    {
        return $this->onAddTeacher(false);
    }

    function onRemoveStudent()
    {
        return $this->onAddStudent(false);
    }

    function onAddTeacher($add = true)
    {

        // validate that the user is admin of this school
        $user = Users::getUser();
        $classid = input('class_id');

        // get the class
        $class = Classes::find($classid);

        // check the user is admin for the related school
        if(!$class || !$class->count()){
            return [
                'valid' => false,
                'message' => 'Deze klas kan niet in ons systeem gevonden worden.'
            ];
        }

        // check if the year of the class is not lower than current year
        if($class->schoolyear < Classes::getCurrentYear()){
            return [
                'valid' => false,
                'message' => 'Deze klas kan niet meer bewerkt worden.'
            ];
        }

        $school = $class->school;

        if(!$user || !$user->isAdminForSchool($school->id)){
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of hebt geen rechten meer voor deze school.',
            ];
        }

        // loop over the teacher IDs
        $teacherids = input('teacher_id');

        if(!is_array($teacherids)){
            $teacherids = array($teacherids);
        }

        foreach($teacherids as $teacherid)
        {

            if(empty($teacherid)){
                return [
                    'valid' => false,
                    'message' => 'Selecteer een leraar.'
                ];
            }

            // get the user
            $teacher = Users::find($teacherid);

            if(!$teacher->count()){
                return [
                    'valid' => false,
                    'message' => 'Deze leraar staat niet in ons systeem.'
                ];
            }

            if(!$teacher->isTeacherForSchool($school->id, $this->param('year')))
            {
                return [
                    'valid' => false,
                    'message' => 'Deze leraar is geen leraar van deze school.'
                ];
            }

            if($add == true)
            {
                // couple the teacher to this class
                $class->teachers()->syncWithoutDetaching([$teacher->id => ['is_teacher' => true]]);
            }
            else {
                $class->teachers()->detach($teacher->id);
            }
        }

        $teachers = $class->teachers()->get();

        // return the update
        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-class-teachers-' . $class->id => $this->renderPartial('examifyHelpers/portal/listOfClassTeachers', 
                        [
                            'class' => $class,
                            'teachers' => $teachers,
                            'form_enabled' => (Classes::getCurrentYear() <= $this->param('year')),
                            'user' => $user
                        ])
            ],
            'debug' => input()
        ];

    }

    function onAddStudent($add = true)
    {

        // validate that the user is admin of this school
        $user = Users::getUser();
        $classid = input('class_id');

        // get the class
        $class = Classes::with('students')->find($classid);

        // check the user is admin for the related school
        if(!$class || !$class->count()){
            return [
                'valid' => false,
                'message' => 'Deze klas kan niet in ons systeem gevonden worden.'
            ];
        }

        // check if the year of the class is not lower than current year
        if($class->schoolyear < Classes::getCurrentYear()){
            return [
                'valid' => false,
                'message' => 'Deze klas kan niet meer bewerkt worden.'
            ];
        }

        $check = $class->addStudent(input('student_id'), $add);
        if($check){ return $check; }

        $students = $class->students()->get();

        // return the update
        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-class-students-' . $class->id => $this->renderPartial('examifyHelpers/portal/listOfClassStudents', 
                        [
                            'class' => $class,
                            'students' => $students,
                            'form_enabled' => (Classes::getCurrentYear() <= $this->param('year'))
                        ])
            ]
        ];

    }
}
