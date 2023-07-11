<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\Classes as Classes;
use Examify\Exams\Models\Texts as Texts;
use Examify\Exams\Models\PracticeSessions as PracticeSessions;
use Examify\Exams\Models\Courses as Courses;
use Examify\Exams\Models\Homework as Homework;
use Examify\Exams\Models\QuestionTypesTrials as QTT;
use Examify\Exams\Models\Questions as Questions;
use Carbon\Carbon as Carbon;

class AdminHomework extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AdminHomework Component',
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

    function onSelectMode()
    {

        // select the mode
        $oefenmodus = 'examenmodus';

        // get the class id
        $classid = input('classid', false);

        if(empty($classid))
        {
            return [
                'valid' => false,
                'message' => 'De klas is niet geselecteerd. Herlaad de pagina.'
            ];
        }

        if($oefenmodus != 'leermodus' && $oefenmodus != 'examenmodus')
        {
            return [
                'valid' => false,
                'message' => 'Selecteer de oefenmodus eerst.'
            ];
        }

        // generate the list of class homework form
        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-class-homework-form-' . $classid => $this->renderPartial('examifyHelpers/portal/formHomework', [
                    'class' => Classes::find($classid),
                    'form_enabled' => (Classes::getCurrentYear() <= $this->param('year')),
                    'PMA' => $oefenmodus == 'leermodus'
                ])
            ]
        ];

    }

    // delete homework
    function onDeleteHomework()
    {
        $user = Users::getUser();
        $confirmed = input('is_confirmed', false);

        if(!$user)
        {
            return [
                'valid' => false,
                'message' => 'Je moet ingelogd zijn.'
            ];
        }

        if($confirmed == -1)
        {
            // render old partial back
            return [
                'valid' => true,
                'updateElement' => [
                    '#modal-homework-name' => '',
                    '#modal-delete-form-input' => '',
                ],
                'call-js-function' => [
                    'controlModal' => ['#delete-homework-modal', 'hide']
                ]
            ];
        }

        // get the homwork id
        $homework_id = input('homework_id', false);

        if(!$homework_id)
        {
            return [
                'valid' => false,
                'message' => 'Dit is geen geldige huiswerkopdracht.'
            ];
        }

        // get the class and validate this is a teacher for this class
        // get the class
        $homework = Homework::with('class')->find($homework_id);

        if(!$homework)
        {
          return [
            'valid' => false,
            'message' => 'Deze huiswerkopdracht is niet gevonden in ons systeem.'
          ];
        }

        // get the class
        $class = $homework->class;

        // validate the user is a teacher
        if(!$user->isAdminForSchool($class->school_id) && !$user->isTeacherForClass($class->id))
        {
            return [
                'valid' => false,
                'message' => 'Je bent geen leraar voor deze klas en je bent ook geen beheerder van de school.'
            ];
        }

        // check if this class year is still valid
        if($homework->due_date < Carbon::now())
        {
            return [
                'valid' => false,
                'message' => 'Alleen openstaand huiswerk kan verwijderd worden'
            ];
        }

        // check if it is confirmed
        if($confirmed == false)
        {
            // show the confirmation boxes
            return [
                'valid' => true,
                'updateElement' => [
                    '#modal-homework-name' => $homework->name,
                    '#modal-delete-form-input' => '<input type="hidden" name="homework_id" value="' . $homework->id . '" />'
                ],
                'call-js-function' => [
                    'controlModal' => ['#delete-homework-modal', 'toggle']
                ]
            ];
        }

        if($confirmed == 1)
        {
            // delete the homework item, and related practice sessions
            PracticeSessions::where('homework_id', $homework->id)->update(['deleted' => 1]);
            $homework->deleted = true;
            $homework->save();

            // check if this was part of a bigger homework entry. If so, also put the parent to deleted if this was the last one of the children that was still left.
            if($homework->parent_id)
            {
                $parent = Homework::find($homework->parent_id);
                if($parent){

                    // in case the parent homework shows this is a split_exam, it is now not anymore, so remove that flag
                    $parent->split_exam = false;

                    if(!$parent->hasChildren()){
                        $parent->deleted = true;
                    }

                    $parent->save();
                }
            }

            return [
                'valid' => true,
                'updateElement' => [
                    '#modal-homework-name' => '',
                    '#modal-delete-form-input' => '',
                    '#list-of-class-homework-' . $class->id => $this->renderPartial('examifyHelpers/portal/listOfClassHomework', [
                        'class' => $class
                    ]),
                ],
                'call-js-function' => [
                    'controlModal' => ['#delete-homework-modal', 'hide']
                ]
            ];
        }
    }

    function isInvalidRegardingLicenses($students, $course_ids, $year)
    {
        // validate each student has access to this course id for this class year
        foreach($students as $student)
        {
            foreach($course_ids as $course_id)
            {
                if(!$student->hasLicenceForCourseIdAndYear($course_id, $year))
                {
                    // get the course
                    $course = Courses::find($course_id);
                    return [
                        'valid' => false,
                        'message' => $student->name . ' heeft geen licentie voor ' . $course->name . ' (' . $course->level . '). Zorg dat alle leerlingen een licentie hebben voor de teksten die opgegeven zijn.'
                    ];
                }
            }
        }

        return false; 
    }

    function onAddHomework()
    {

        $user = Users::getUser();

        if(!$user)
        {
            return [
                'valid' => false,
                'message' => 'Je moet ingelogd zijn.'
            ];
        }

        $ndates = input('ndates', 1);

        if(is_array($ndates)){
            $ndates = end($ndates);
        }

        // check if there needs to be a time added or removed
        $extraData = input('extraData', false);

        if($extraData){
            $extraData = json_decode($extraData, true);

            if(array_key_exists('addTime', $extraData))
            {
                if($ndates > 4){
                    return [
                        'valid' => false,
                        'message' => 'Er is iets fout gegaan met datums toevoegen.'
                    ];
                }

                if($extraData['addTime']){
                    $newdate = $ndates + 1;
                    $newn = $ndates + 1;
                    $removeTime = false;
                }
                else {
                    $newdate = $ndates;
                    $newn = $ndates - 1;
                    $removeTime = true;
                }

                return [
                    'valid' => true,
                    'updateElement' => [
                        '#control-dates-' . $newdate => $this->renderPartial('examifyHelpers/portal/formHomeworkDates', [
                            'ndates' => $newn,
                            'removeTime' => $removeTime
                        ])
                    ]
                ];
            }
        }

        // get the class id
        $class_id = input('class_id', false);

        if(!$class_id)
        {
            return [
                'valid' => false,
                'message' => 'Dit is geen geldige klas.'
            ];
        }

        // get the class and validate this is a teacher for this class
        // get the class
        $class = Classes::find($class_id);

        if(!$class)
        {
          return [
            'valid' => false,
            'message' => 'Deze klas is niet gevonden in ons systeem.'
          ];
        }

        // validate the user is a teacher
        if(!$user->isAdminForSchool($class->school_id) && !$user->isTeacherForClass($class->id))
        {
            return [
                'valid' => false,
                'message' => 'Je bent geen leraar voor deze klas en je bent ook geen beheerder van de school.'
            ];
        }

        // check if this class year is still valid
        if($class->schoolyear < Classes::getCurrentYear())
        {
            return [
                'valid' => false,
                'message' => 'Alleen van het huidige schooljaar en later kan het huiswerk opgegeven worden.'
            ];
        }

        // validate the description
        $desc = input('name', false);

        if(empty($desc))
        {
            return [
                'valid' => false,
                'message' => 'Geef deze huiswerkopdracht een naam zodat je deze makkelijk later terug kunt vinden.'
            ];
        }

        // check if the date is correct (later than now)
        $mydates = input('date', false);
        $mytimes = input('time', false);

        // validate it is valid
        if(empty($mydates) || empty($mytimes))
        {
            return [
                'valid' => false,
                'message' => 'Dit is geen geldige deadline. Controleer of je zowel datum als tijd hebt ingevuld.'
            ];
        }

        if(count($mydates) != $ndates || count($mytimes) != $ndates)
        {
            return [
                'valid' => false,
                'message' => 'Vul voor elke deadline een datum en tijd in.'
            ];
        }

        $mydatetimes = collect([]);

        for($ii = 0; $ii < $ndates; $ii++)
        {

            if(empty($mytimes[$ii]) || empty($mydates[$ii]))
            {
                return [
                    'valid' => false,
                    'message' => 'Vul voor elke deadline een datum en tijd in.'
                ];
            }

            $t = Carbon::createFromFormat('d-m-Y H:i', $mydates[$ii] . ' ' . $mytimes[$ii])->toDateTimeString();

            // double check the date is after now
            if($t < Carbon::now())
            {
                return [
                    'valid' => false,
                    'message' => 'Deze deadline is al geschiedenis. Kies een datum en tijd die in de toekomst ligt. Wel zo eerlijk voor de leerlingen :).'
                ];
            }

            $mydatetimes->push($t);
        }

        $mydatetimes = $mydatetimes->sort()->values()->all();

        // get all the course ids and validate that each student is subscribed for those courses
        $student_ids = input('student_id', false);

        if(empty($student_ids))
        {
            return [
                'valid' => false,
                'message' => 'Selecteer minstens 1 leerling.'
            ];
        }

        // validate they are all students of this class
        if(!$class->areAllStudentsOfThisClass($student_ids))
        {
            return [
                'valid' => false,
                'message' => 'Niet alle leerlingen behoren tot deze klas.'
            ];
        }

        // get all the texts that are selected
        $text_ids = input('text_id', []);
        $trial_ids = input('trial_id', []);

        if(empty($text_ids) && empty($trial_ids))
        {
            return [
                'valid' => false,
                'message' => 'Selecteer minstens 1 tekst of vraagtype trial als huiswerkopdracht.'
            ];
        }

        // validate the licenses for these students
        $students = Users::find($student_ids);

        $hasexams = false;
        $hastrials = false;
        $texts = collect([]);
        $trials = collect([]);
        $questionIDs = collect([]);
        $exam_id = 0;   // default, no exam_id stored
        $partial_exam_id = 0;
        $course_id = 0;

        if(!empty($text_ids)){

            // validate all text ids are valid
            $texts = Texts::with('exam', 'exam.course')->find($text_ids);

            // check if all texts come from the same exam
            $myexams = $texts->pluck('exam')->unique();
            if($myexams->count() == 1)
            {

                $course_id = $myexams->first()->course_id;

                // check if all texts in this exam are selected, in that case, a full exam is selected and grades can be added later
                if($myexams->first()->getTexts()->count() == count($text_ids))
                {
                    // all texts are selected
                    $exam_id = $myexams->first()->id;
                }
                else {
                    $partial_exam_id = $myexams->first()->id;
                }

            }

            if($exam_id == 0 && $ndates > 1)
            {
                return [
                    'valid' => false,
                    'message' => 'Als er meerdere deadlines zijn opgegeven dan moet het huiswerk een volledig examen betreffen. Dat is nu niet het geval.'
                ];  
            }

            // check if all exams are visible
            foreach($myexams as $e)
            {
                if(!$e->visible){
                    return [
                        'valid' => false,
                        'message' => 'Er is iets fout gegaan met het selecteren van teksten. Probeer opnieuw door de pagina te vernieuwen.'
                    ];
                }
            }

            if(!$texts->count())
            {
                return [
                    'valid' => false,
                    'message' => 'Deze teksten zijn niet in ons systeem gevonden.'
                ];
            }

            // check all the course ids that are belonging to these texts
            $course_ids = $texts->pluck('exam.course')->unique()->pluck('id');

            $invalid = $this->isInvalidRegardingLicenses($students, $course_ids, $class->schoolyear);
            if($invalid){
                return $invalid;
            }

            // get the questions
            $textQuestions = $texts->pluck('questions')->flatten();

            if(!$textQuestions->count())
            {
                return [
                    'valid' => false,
                    'message' => 'Er zijn nog geen vragen gekoppeld aan deze teksten.'
                ];
            }

            // store all the ids of the questions
            $questionIDs = $textQuestions->pluck('id');
            $hasexams = true;

        }

        if(!empty($trial_ids))
        {

            if($ndates != 1){
                return [
                    'valid' => false,
                    'message' => 'Als er meerdere deadlines zijn ingesteld dan kunnen er alleen hele examens worden opgegeven als huiswerk. Er zijn nu ook vraagtypen trials opgegeven.'
                ];
            }

            // get the courses of these trials, and make sure that all students have licenses for it
            $trials = QTT::find($trial_ids)->where('visible', true);

            if(!$trials){
                return [
                    'valid' => false,
                    'message' => 'Er is geen vraagtype trial gevonden in ons systeem behorende bij de selectie.'
                ];
            }

            $course_ids = $trials->pluck('course_id')->unique();
            $invalid = $this->isInvalidRegardingLicenses($students, $course_ids, $class->schoolyear);
            if($invalid){
                return $invalid;
            }

            $hastrials = true;

        }
    

        // check what is the leermodus
        $leermodus = false;     // only examenmodus is supported yet
        $leermodus = !!$leermodus;

        // check the time_limit in minutes
        $time_limit = input('time_limit_mins', 0);
        if($time_limit)
        {
            $valid = true;
            if(intval($time_limit) != $time_limit)
            {
                $valid = false; 
                $message = 'Geef de tijdslimiet op in hele minuten. Bijvoorbeeld in het geval van 2.5 uur: 150';
            }
            if($time_limit < 1)
            {
                $valid = false;
                $message = 'De tijdslimiet moet minstens 1 minuut zijn.';
            }
            if(!$valid)
            {
                return [
                    'valid' => false,
                    'message' => $message
                ];
            }
        }
        else {
            $time_limit = 0;
        }

        // for each date there is an extra entry
        $nentries = $hasexams + $trials->count() + ($ndates - 1);

        // in case ndates > 1, it means there is a full exam, and the questionIDs need to be split up 
        if($ndates > 1)
        {
            $q_per_text = $texts->pluck('questions');
            $totalpoints = $q_per_text->flatten()->sum('points');

            // here, a margin of 2 points is applied since otherwise parts tend to grow too quickly in points.
            $targetpoints = floor($totalpoints / $ndates);

            $addedpoints = 0;
            $isplit = 0;
            $qsplit = array(collect([]));
            $tsplit = array(collect([]));

            foreach($texts as $tindex => $t){

                $qs = $t->questions;

                $addedpoints += $qs->sum('points');
                $qsplit[$isplit]->push($qs->pluck('id'));
                $tsplit[$isplit]->push($t);

                // check if once the next part is included the gap to the target points is bigger, in that case include already this one.

                if( ($isplit + 1) < $ndates){
                    
                    $nextpoints = $addedpoints + $texts[$tindex + 1]->questions->sum('points');
                    if(($nextpoints - $targetpoints) > ($targetpoints - $addedpoints)){
                        $qsplit[$isplit] = $qsplit[$isplit]->flatten();
                        $isplit++;
                        $qsplit[] = collect([]);
                        $tsplit[] = collect([]);
                        $addedpoints = 0;
                    }

                }

            }

            // the last one should be flattened since it is not done in the foreach loop
            $qsplit[$ndates - 1] = $qsplit[$ndates - 1]->flatten();

        }
        else {
            $qsplit = array($questionIDs);
            $tsplit = array($texts);
        }

        // add a main one if the number of entries is larger than 1
        $nentries = $nentries > 1 ? $nentries + 1 : $nentries;
        $itrial = 0;

        $isplit = 0;

        // get the last date as the overall due date
        $last_date = end($mydatetimes);

        $split_exam = $ndates > 1 ? true : false;

        for($ii = 0; $ii < $nentries; $ii++)
        {
            $hw = new Homework();

            $hw->due_date = $last_date;
            $hw->school_id = $class->school_id;
            $hw->class_id = $class->id;
            $hw->name = $desc;
            $hw->leermodus = $leermodus;
            $hw->time_limit_mins = $time_limit;
            $hw->name = $desc;
            $hw->split_exam = $split_exam;
            $hw->course_id = $course_id;     // default equal to the found course id via the exams (which is 0 in case multiple exams are found)
            $hw->exam_id = $exam_id;

            if($ndates > 1)
            {
                // add the number of texts and questions to this main homework session
                $hw->ntexts = count($texts);
                $hw->nquestions = $texts->pluck('questions')->flatten()->count();
            }

            // add the texts and questions in case the entry belongs to the exams
            if($hasexams){
                if(($nentries > 1 && $ii == 1) || ($nentries == 1) || ($ndates > 1 && $ii >= 1)){

                    // this corresponds to the standard exam entry. Add the texts and questions
                    $hw->ntexts = $tsplit[$isplit]->count();
                    $hw->nquestions = count($qsplit[$isplit]);
                    $hw->texts = $tsplit[$isplit]->pluck('id');
                    $hw->questions = $qsplit[$isplit];
                    $hw->question_types_trials_id = 0;

                    // update the due date based on the split
                    $hw->due_date = $mydatetimes[$isplit];

                    if($ndates > 1){
                        $hw->name = $desc . ' (' . (intval($isplit) + 1) . '/' . $ndates . ')';
                    }

                    $isplit++;
                }
            }

            if($hastrials){
                if(($hasexams && $ii > 1) || (!$hasexams && $ii > 0)){
                    // this corresponds to a question type trial entry, so specify only the questions regarding to this trial.
                    $qids = collect($trials[$itrial]->question_ids);
                    $hw->questions = $qids;
                    $hw->nquestions = count($qids);
                    $hw->question_types_trials_id = $trials[$itrial]->id;

                    // do not specify a time limit
                    $hw->time_limit_mins = 0;
                    $hw->course_id = $trials[$itrial]->course_id;
                    $itrial++;
                }
            }

            // get the totalpointsavailable 
            $q = Questions::find($hw->questions);
            if($q){
                $hw->totalpointsavailable = $q->sum('points');
            }

            $hw->save();
            $hwlist[] = $hw;
        }

        // in case there is a split, it should be all partial exam ids
        if($ndates > 1){
            $partial_exam_id = $exam_id;
            $exam_id = 0;
        }

        // in case there is more than one element in the hwlist, make the first one the main, and the other the childs
        if(count($hwlist) > 1){
            $parent = $hwlist[0];
            unset($hwlist[0]);

            $child_ids = [];
            foreach($hwlist as $hw){
                $hw->parent_id = $parent->id;
                $child_ids[] = $hw->id;
                $hw->save();
            }

            $parent->child_ids = $child_ids;
            $parent->save();
        }

        // create for each user a practice session. All is now validated, and hwlist only contains the real homework, not the parent anymore (in case multiple childs are present).
        foreach($students as $student)
        {
            $pslist = [];

            foreach($hwlist as $hw)
            {
                $ps = new PracticeSessions();

                $ps->user_id = $student->id;
                $ps->exam_id = $exam_id;
                $ps->partial_exam_id = $partial_exam_id;
                $ps->question_types_trials_id = $hw->question_types_trials_id;
                $ps->time_limit_mins = $hw->time_limit_mins;

                $ps->finished = false;
                $ps->leermodus = $leermodus;
                $ps->started = false;

                $ps->due_date = $hw->due_date;
                $ps->homework_id = $hw->id;

                $ps->question_id = $hw->questions;
                $ps->totalpointsavailable = $hw->totalpointsavailable;

                // required for at least the question types trials and full exams
                $ps->course_id = $hw->course_id;

                $ps->save();
                $pslist[] = $ps;

            }
        }

        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-class-homework-' . $class->id => $this->renderPartial('examifyHelpers/portal/listOfClassHomework', [
                    'class' => $class
                ]),
                '#list-of-class-homework-form-' . $class->id => $this->renderPartial('examifyHelpers/portal/formHomework', [
                    'class' => $class,
                    'form_enabled' => true
                ])
            ]
        ];

    }
}
