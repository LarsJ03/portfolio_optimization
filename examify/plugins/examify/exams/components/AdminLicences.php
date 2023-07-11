<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use \Examify\Exams\Models\Schools as Schools;
use \Examify\Exams\Models\Users as Users;
use \Examify\Exams\Models\Licenses as Licences;
use \Examify\Exams\Models\Courses as Courses;
use \Examify\Exams\Models\Classes as Classes;

class AdminLicences extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AdminLicences Component',
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

        // get the current school
        $school = Schools::find($this->property('school_id'));

        if(!$school->count()){
            return;
        }

        // check if the user is admin of this school
        $user = Users::getUser();

        if(!$user || !$user->isAdminForSchool($school->id))
        {
          return redirect('/login')->with('redirect-to', url()->current());
        }

        // set the school
        $this->page['school'] = $school;

        // set the schoolyear
        $this->page['schoolyear'] = $this->param('year');
        $this->page['school_id'] = $school->id;

        // set the courses
        $this->page['courses'] = Courses::all();


    }

    function onSelectClass()
    {

        $school = Schools::find(input('school_id'));

        if(!$school->count()){
            return;
        }

        // check if the user is admin of this school
        $user = Users::getUser();

        if(!$user || !$user->isAdminForSchool($school->id))
        {
            return [
                'valid' => false,
                'message' => 'Je bent geen beheerder van deze school.',
                'updateElement' => [ 
                    '#licence-students-' . $school->id => '',
                    '#licence-students-confirmation-' . $school->id => ''
                ]
            ];
        }

        $currentyear = Classes::getCurrentYear();

        // check which classes are selected
        $classids = input('classids');

        if(!is_array($classids)){
            return [
                'valid' => false,
                'message' => 'Selecteer minstens 1 klas.',
                'updateElement' => [ 
                    '#licence-students-' . $school->id => '',
                    '#licence-students-confirmation-' . $school->id => ''
                ]
            ];
        }

        $nclasses = count($classids);

        // make sure that the classes all belong to this school
        foreach($classids as $classid)
        {
            // get the class
            $class = Classes::with('course')->find($classid);
            if(!$class || $class->school_id != $school->id)
            {
                return [
                    'valid' => false,
                    'message' => 'Niet alle klassen horen bij deze school.',
                    'updateElement' => [ 
                        '#licence-students-' . $school->id => '',
                        '#licence-students-confirmation-' . $school->id => '' 
                    ]
                ];
            }

            if(!$class->isCurrentOrNextYear())
            {
                return [
                    'valid' => false,
                    'message' => 'Deze klas is niet van huidig of volgend schooljaar.',
                    'updateElement' => [ 
                        '#licence-students-' . $school->id => '',
                        '#licence-students-confirmation-' . $school->id => '' 
                    ]
                ];
            }
        }

        // in case there is only 1 class id selected, give the students belonging to this class
        if($nclasses > 1)
        {
            return [
                'valid' => true,
                'updateElement' => [
                    '#licence-students-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfStudentsToLicence', [
                        'nclasses' => $nclasses,
                        'classes' => Classes::find($classids),
                        'classids' => serialize($classids),
                        'schoolid' => $school->id
                    ]),
                    '#licence-students-confirmation-' . $school->id => ''
                ]
            ];
        }

        $students = $class->getStudents();
        if($students->count() == 0){
            return [
                'valid' => false,
                'message' => 'Er zijn nog geen leerlingen aan deze klas gekoppeld.',
                'updateElement' => [ 
                    '#licence-students-' . $school->id => '',
                    '#licence-students-confirmation-' . $school->id => ''
                ]
            ];
        }

        return [
            'valid' => true,
            'updateElement' => [
                '#licence-students-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfStudentsToLicence', [
                    'students' => $class->getStudents(),
                    'class' => $class,
                    'schoolid' => $school->id
                ]),
                '#licence-students-confirmation-' . $school->id => ''
            ]
        ];

    }

    public function onConfirmStudents()
    {

        // check if the user is admin of this school
        $user = Users::getUser();

        $school = Schools::find(input('school_id'));

        if(!$school || !$user || !$user->isAdminForSchool($school->id))
        {
            return [
                'valid' => false,
                'message' => 'Je bent geen beheerder van deze school.',
                'updateElement' => [ 
                    '#licence-students-' . $school->id => '',
                    '#licence-students-confirmation-' . $school->id => ''
                ]
            ];
        }

        // get the mode
        $hasclasses = input('has_classes', 0);
        $hasstudents = input('has_students', 0);

        if(!$hasclasses && !$hasstudents){
            return [
                'valid' => false,
                'message' => 'Er is iets mis gegaan. Probeer het opnieuw.'
            ];
        }

        if($hasclasses)
        {

            // get the classids
            $classids = unserialize(input('classids'));

            $classes = Classes::find($classids);

            // loop over the classes to see if the school id is correct
            foreach($classes as $class)
            {
                if($class->school_id != $school->id)
                {
                    return [
                        'valid' => false,
                        'message' => 'Er is iets mis gegaan. Probeer het opnieuw.'
                    ];
                }

                if(!$class->isCurrentOrNextYear())
                {
                    return [
                        'valid' => false,
                        'message' => 'De klassen licentie moet voor huidig of volgend schooljaar zijn.'
                    ];
                }

                // get the students in this class
                $students = $class->getStudents();
                $mycourse = $class->course;

                $nlics = 0;

                // loop over the students and check if the course associated to this class is new
                foreach($students as $student)
                {
                    if($student->hasLicenceForCourseIdAndYear($mycourse->id, $class->schoolyear)){
                        continue;
                    }

                    // add the student to the to be licenced list
                    $studentstobelicenced[$mycourse->id][] = $student->id;
                    $nlics = $nlics + 1;

                }

            }

            // remove the double ones (in case a student is in multiple classes)
            if($nlics != 0){

                // update the nlics 
                $nlics = 0;

                foreach($studentstobelicenced as $key => $i)
                {
                    $update = array_unique($i);
                    $studentstobelicenced[$key] = $update;
                    $nlics = $nlics + count($update);
                }
            }

        }

        if($hasstudents)
        {
            $studentids = input('studentids');

            if(!is_array($studentids))
            {
                return [
                    'valid' => false,
                    'message' => 'Selecteer minstens 1 leerling.'
                ];
            }

            // check if all students can be found
            $students = Users::find($studentids);

            // get the classid
            $classid = input('class_id', 0);
            $class = Classes::find($classid);
            if(!$class){
                return [
                    'valid' => false,
                    'message' => 'Er is iets mis gegaan. Probeer het opnieuw.'
                ];
            }

            // verify this class belongs to this school
            if($class->school_id != $school->id)
            {
                return [
                    'valid' => false,
                    'message' => 'Deze klas hoort niet bij deze school.'
                ];
            }

            // get the schoolyear
            $schoolyear = $class->schoolyear;

            // check that the class year can be licenced
            $currentyear = Classes::getCurrentYear();

            if(!$class->isCurrentOrNextYear())
            {
                return [
                    'valid' => false,
                    'message' => 'Deze klas is niet van huidig of volgend schooljaar.'
                ];
            }

            // check that the studentids are all from this class
            $check = $students->intersect($class->getStudents())->count() == $students->count();
            if(!$check)
            {
                return [
                    'valid' => false,
                    'message' => 'Niet alle leerlingen horen bij deze klas.',
                ];
            }

            // check the course of the class
            $course = $class->course;

            // get the level, and check if the other level is also checked
            $level = $course->level;

            $do['vwo'] = input('level_vwo', false);
            $do['havo'] = input('level_havo', false);
            $do['mavo'] = input('level_mavo', false);

            // overwrite for the course level
            $do[$level] = true;
            $coursetolicence[$level] = $course;

            $mylevels = array('mavo', 'havo', 'vwo');

            // loop over all the levels and add the course to license in case
            foreach($mylevels as $mylevel)
            {
                // skip if the current course is already of this level
                if($course->level == $mylevel){ continue; }

                // store which course to license
                if($do[$mylevel])
                {
                    $othercourse = Courses::where('level', $mylevel)->where('name', $course->name)->get();

                    if($othercourse->count() != 1)
                    {
                        return [
                            'valid' => false,
                            'message' => 'Er is in ons systeem geen ' . $course->name . ' ' . $level . ' gevonden.'
                        ];
                    }

                    $coursetolicence[$mylevel] = $othercourse->first();

                }
            }

            $nlics = 0;

            // loop over the courses to licence
            foreach($coursetolicence as $mycourse)
            {

                // check if the student is already assigned to this course
                foreach($students as $student)
                {
                    if($student->hasLicenceForCourseIdAndYear($mycourse->id, $schoolyear)){
                        continue;
                    }

                    // add the student to the to be licenced list
                    $studentstobelicenced[$mycourse->id][] = $student->id;

                    // count the licences
                    $nlics = $nlics + 1;

                }

            }

        }

        // in case the number of licences is bigger than 0, get the courses for which the licences need to be generated
        if($nlics > 0)
        {
            foreach($studentstobelicenced as $key => $i)
            {
                $courseids[] = $key;
                $nlicspercourse[] = count($i);
            }

            // get the courses
            $mycourses = Courses::find($courseids);

        }
        else {
            $studentstobelicenced = [];
            $mycourses = [];
            $nlicspercourse = 0;
        }

        return [
            'valid' => true,
            'updateElement' => [
                '#licence-students-confirmation-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfLicencesToBeGenerated', [
                    'studentstobelicenced' => serialize($studentstobelicenced),
                    'nlics' => $nlics,
                    'school' => $school,
                    'mycourses' => $mycourses,
                    'nlicspercourse' => $nlicspercourse
                ])
            ]
        ];
    }

    // check the licences
    function onConfirmLicences()
    {

        // get the year
        $schoolyear = $this->param('year', false);

        if(!Classes::schoolyearIsCurrentOrNextYear($schoolyear))
        {
            return [
                'valid' => false,
                'message' => 'Voor dit schooljaar kunnen geen licenties meer aangeschaft worden.'
            ];
        }

        // check if the user is admin of this school
        $user = Users::getUser();

        $school = Schools::find(input('school_id'));

        if(!$school || !$user || !$user->isAdminForSchool($school->id))
        {
            return [
                'valid' => false,
                'message' => 'Je bent geen beheerder van deze school.',
                'updateElement' => [ 
                    '#licence-students-' . $school->id => '',
                    '#licence-students-confirmation-' . $school->id => ''
                ]
            ];
        }

        // loop over the users, and check that all students are part of the school
        $studentstobelicenced = input('studentstobelicenced', false);

        if($studentstobelicenced == false)
        {
            return [
                'valid' => false,
                'message' => 'Er is iets mis gegaan. Probeer opnieuw.'
            ];
        }

        $studentstobelicenced = unserialize($studentstobelicenced);

        if(!is_array($studentstobelicenced)){
            return [
                'valid' => false,
                'message' => 'Er is iets mis gegaan. Probeer opnieuw.'
            ];
        }

        // loop over the courses, and licence the students
        foreach($studentstobelicenced as $courseid => $students)
        {

            // loop over the students
            foreach($students as $studentid)
            {

                // get the student
                $student = Users::find($studentid);

                // validate this user belongs to this school
                if(!$student->isStudentForSchool($school->id, $schoolyear));

                if($student->hasLicenceForCourseIdAndYear($courseid, $schoolyear)){
                    continue;
                }

                // create the licence for this school and course
                $newLic = new Licences();
                $newLic->user_id = $student->id;
                $newLic->schoolyear = $schoolyear;
                $newLic->course_id = $courseid;
                $newLic->generateKey();
                $newLic->activated = true;
                $newLic->school_id = $school->id;
                $newLic->save();

            }

        }

        // everything is ok
        return [
            'valid' => true,
            'showElement' => [
                '#success-message-' . $school->id,
            ],
            'hideElement' => [
                '#confirm-button-' . $school->id
            ]
        ];

    }

}
