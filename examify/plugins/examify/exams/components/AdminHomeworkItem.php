<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use \Examify\Exams\Components\Charts as ChartsBase;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\Homework as Homework;
use Examify\Exams\Models\Classes as Classes;
use Examify\Exams\Models\PracticeSessionsResultsLogs as PracticeSessionsResultsLogs;
use Examify\Exams\Models\PracticeSessions as PracticeSessions;
use Examify\Exams\Models\Questions as Questions;
use Examify\Exams\Models\Texts as Texts;
use Examify\Exams\Models\QuestionsAnswersLogs as QuestionsAnswersLogs;
use Examify\Exams\Models\Answers as Answers;
use Examify\Exams\Models\Exams as Exams;
use Str;
use Carbon\Carbon;

class AdminHomeworkItem extends ChartsBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AdminHomeworkItem Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public $user;
    public $homework;
    public $validationpassed;

    function validateMe()
    {
        $this->user = $user = Users::getUser();

        // check if the user administrates this class
        if(!$user){
            return redirect('/login');
        }

        // get the homework
        $homework = Homework::find($this->param('homeworkid'));

        if(!$homework)
        {
            return redirect('/portal/classes/' . Classes::getCurrentYear());
        }

        // check if the user is authorized for this class
        if(!$user->isAdminForSchool($homework->class->school->id) && !$user->isTeacherForClass($homework->class->id)){
          return redirect('/portal/classes/' . Classes::getCurrentYear());
        }

        $this->homework = $homework;
        $this->validationpassed = true;
    }

    function onRender()
    {

        $this->page['show_homework'] = false;

        $this->validateMe();

        if(!$this->user)
        {
            return;
        }

        $homework = $this->homework;

        $ps = $homework->getPracticeSessions();
        $ps = $ps->first();

        $this->page['isfullexam'] = $isfullexam = ($homework->exam_id > 0 && $homework->parent_id == 0);
        if($isfullexam)
        {
            $exam = Exams::find($homework->exam_id);
            $this->page['nterm'] = $exam->norm;
        }

        // set the link back
        $this->page['url_back'] = '/portal/classes/' . $homework->class->schoolyear . '/' . $homework->class->id . '/homework';

        $this->page['show_homework'] = true;

        // get all the filter settings
        $this->page['available_filters'] = $this->getQuestionFilters();
        $this->page['hideNames'] = $this->user->getUserSetting('RandomNames');

        $this->onApplyFilter();

    }

    function onEditStudents()
    {
        $this->validateMe();

        if(!$this->user)
        {
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd. Log opnieuw in.'
            ];
        }

        if(!$this->validationpassed)
        {
            return [
                'valid' => false,
                'message' => 'Je hebt geen rechten om deze huiswerkopdracht aan te passen.'
            ];
        }

        $include_user = input('include_user', [0 => 1]);

        // set all practicesessions that do not have these users ids to deleted
        $hw = $this->homework;
        $ps = $hw->practiceSessions()->whereNotIn('user_id', array_keys($include_user))->get();

        if($ps->count() > 0)
        {
            // loop over the practice sessions that are not active anymore, and soft delete them
            foreach($ps as $p)
            {
                $p->deleted = true;
                $p->save();
            }
        }

        // for each of the included users, get the practicesession, and if it is not there yet, create one
        $refsession = $hw->practiceSessions()->first();

        $include_user = array_keys($include_user);
        if($include_user == array(0))
        {
            return [
                'valid' => true,
            ];
        }

        // create copies of not exist, and otherwise set the practicesession to active again
        foreach($include_user as $user_id)
        {
            $ps = $hw->getSessionForUserID($user_id);

            if($ps)
            {
                $ps->deleted = false; 
                $ps->save();
                continue;
            }

            // create a replica and set all to default. This user did not have yet a session assigned to it.
            $ps = $refsession->replicate();
            $ps->user_id = $user_id;
            $ps->finished = 0;
            $ps->totalpointsachieved = null;
            $ps->created_at = Carbon::now();
            $ps->updated_at = Carbon::now();
            $ps->mark = null;
            $ps->started = 0;
            $ps->deleted = 0;
            $ps->start_time = null;
            $ps->end_time = null;
            $ps->totalpointsunanswered = null;
            $ps->save();
        }

        return [
            'valid' => true
        ];
    }

    function onEditHomework()
    {
        $this->validateMe();

        if(!$this->user)
        {
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd. Log opnieuw in.'
            ];
        }

        if(!$this->validationpassed)
        {
            return [
                'valid' => false,
                'message' => 'Je hebt geen rechten om deze huiswerkopdracht aan te passen.'
            ];
        }

        // check if the date is correct (later than now)
        $mydate = input('date', false);
        $mytime = input('time', false);

        // validate it is valid
        if(empty($mydate) || empty($mytime))
        {
            return [
                'valid' => false,
                'message' => 'Dit is geen geldige deadline. Controleer of je zowel datum als tijd hebt ingevuld.'
            ];
        }

        $mydatetime = Carbon::createFromFormat('d-m-Y H:i', $mydate . ' ' . $mytime)->toDateTimeString();

        // double check the date is after now
        if($mydatetime < Carbon::now())
        {
            return [
                'valid' => false,
                'message' => 'Deze deadline is al geschiedenis. Kies een datum en tijd die in de toekomst ligt. Wel zo eerlijk voor de leerlingen :).'
            ];
        }

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

        // update the homework item name
        $desc = input('name', false);

        if(empty($desc))
        {
            return [
                'valid' => false,
                'message' => 'Geef deze huiswerkopdracht een omschrijving zodat je deze makkelijk later terug kunt vinden.'
            ];
        }

        $hw = $this->homework;
        $hw->name = $desc;
        $hw->due_date = $mydatetime;
        $hw->time_limit_mins = $time_limit;
        $hw->save();

        // now update all the related practicesessions
        foreach($hw->practiceSessions as $ps)
        {
            $ps->due_date = $mydatetime;
            $ps->time_limit_mins = $time_limit;
            $ps->save();
        }

        return [
            'valid' => true,
        ];
    }

    function onApplyFilter($output_element = '#list-of-homework-questions-output')
    {

        // validation
        $this->validateMe();

        if(!$this->user)
        {
            return [];
        }

        // get the inputs
        $user_ids   = input('user_ids', []);
        $atypes     = input('atypes', []);
        $type_ids   = input('type_ids', []);

        $validfilter = true;

        if(input('filter_via_form', false))
        {



            // in case there is filtered via the form, it cannot be that one of them is empty
            if(empty($user_ids))
            {
                $validfilter = false;
                $filtertext = 'Selecteer minstens 1 leerling.';
            }

            if(empty($atypes))
            {
                $validfilter = false;
                $filtertext = 'Selecteer minstens 1 antwoordtype.';
            }

            if(empty($type_ids))
            {
                $validfilter = false;
                $filtertext = 'Selecteer minstens 1 vraagtype';
            }

            if(!$validfilter)
            {
                return [
                    'valid' => true,
                    'updateElement' => [
                        $output_element => $this->renderPartial('examifyHelpers/portal/listOfHomeworkQuestions', [
                            'listofquestions_bytext' => []
                        ]),
                        '#filter-response-text' => $filtertext
                    ],
                    'showElement' => [
                        '#filter-response-text'
                    ]
                ];
            }
        }
        
        // apply the filter and generate the results
        $this->page['listofquestions'] = $listofquestions = $this->getListOfQuestions($type_ids, $atypes, $user_ids);

        // in case of no item is found with this filter, return immediately
        if(empty($listofquestions))
        {
            return [
                'valid' => true,
                'updateElement' => [
                    $output_element => $this->renderPartial('examifyHelpers/portal/listOfHomeworkQuestions', [
                        'listofquestions' => []
                    ])
                ],
                'hideElement' => [
                    '#filter-response-text'
                ]
            ];
        }

        // render the partial in case it is defined
        if(!empty($output_element))
        {
            return [
                'valid' => true,
                'updateElement' => [
                    $output_element => $this->renderPartial('examifyHelpers/portal/listOfHomeworkQuestions', [
                        'listofquestions' => $listofquestions,
                        'hideNames' => $this->user->getUserSetting('RandomNames')
                    ])
                ],
                'hideElement' => [
                    '#filter-response-text'
                ]
            ];
        }

    }

    function onGetStatisticsPerQuestionType()
    {

        // validation
        $this->validateMe();

        if(!$this->user){
            return;
        }

        // get all the logs
        $allLogs = $this->getAllLogs();

        return [
            'valid' => false,
            'data' => $allLogs,
            'id' => $this->param('homework_id')
        ];
    }

    function onGetQuestionTypesOverview()
    {

        $this->validateMe();

        $practicesessions = $this->homework->getPracticeSessions();
        $homework = $this->homework;

        // get the number of splits of this homework
        if($homework->child_ids){
            $scaling = count($homework->child_ids);
        }
        else {
            $scaling = 1;
        }

        // loop over them to update the cached points if not yet generated
        $cp = $practicesessions->pluck('cached_points')->flatten(1);
        $logids = $cp->pluck('log_id');


        // get all practicesession results logs for these practice sessions
        $allLogs = PracticeSessionsResultsLogs::with('questionType')->find($logids);

        if(!$allLogs || !$allLogs->count())
        {
            return [
                'valid' => false,
                'showElement' => [
                    '#placeholder-chart-question-types'
                ],
                'hideElement' => [
                    '#questionTypesChart'
                ]
            ];
        }

        // now return the getChartDataQuestionTypes
        return $this->getChartDataQuestionTypes($allLogs, 'Goed', 'Fout', 'Onbeantwoord', $scaling);

    }

    function getQuestionFilters()
    {
        $practicesessions = $this->homework->getPracticeSessions()->where('finished', true);

        if(!$practicesessions->count())
        {
            return [];
        }  

        // get all the available users
        $user_ids = $practicesessions->pluck('user_id')->values();
        $users = Users::find($user_ids)->sortBy('surname');

        // get the question types. Do this via the questions. This is equal for all sessions
        $questionIDs = $this->homework->getQuestions();
        $myQuestions = Questions::with('type')->find($questionIDs);
        $qtypes = $myQuestions->pluck('type')->unique()->values();

        // get the answer types
        $atypes = $myQuestions->pluck('answer_type')->unique()->values();

        // convert the answer types to display names
        $atypesdisplay = Questions::getNamesForAnswerTypes();

        return [
            'users' => $users,
            'qtypes' => $qtypes,
            'atypes' => $atypes,
            'atypesdisplay' => $atypesdisplay
        ];
    }

    function getListOfQuestions($type_ids = [], $answer_types = [], $user_ids = [])
    {
        $practicesessions = $this->homework->getPracticeSessions()->where('finished', true);   

        return PracticeSessions::getListOfQuestions($practicesessions, $type_ids, $answer_types, $user_ids);
    }

    function onGetPointsPerStudent()
    {
        // get the homework id
        $this->validateMe();

        // get the element id
        $element = input('data_form', false);
        if(!$element){ return; }

        // get all the practicesessions belonging to this homework
        $practicesessions = $this->homework->getPracticeSessionsWithStudentNameAndPoints()->where('finished', true);

        // order it by student name
        if(!$practicesessions->count())
        {
            return [
                'valid' => false,
                'showElement' => [
                    '#placeholder-chart-points'
                ],
                'hideElement' => [
                    '#studentPointsChart'
                ]
            ];
        }

        // sort them
        $practicesessions = $practicesessions->sortBy('surname');

        // in case of superadmin, random names
        /*
        if($this->user->isSuperAdmin())
        {
            $labels = [];
            foreach($practicesessions as $i)
            {
                $labels[] = Str::random(10);
            }
        }
        else {
            $labels = $practicesessions->pluck('studentname');
        }
        */

        $labels = [];

        // loop over the practice sessions and add to the labels the total duration
        foreach($practicesessions as $ps)
        {
            if($this->user->getUserSetting('RandomNames'))
            {
                $ps->studentname = Str::random(10);
            }

            $ps->timespent = $ps->getTimeSpent();
        }

        // in case a full exam is given, transform the points into 1 to 10
        if($practicesessions->first()->isFullExam())
        {
            $nterm = $practicesessions->first()->exam->norm;

            foreach($practicesessions as $ps)
            {
                // transform the points to marks
                $pac = $ps->totalpointsachieved;
                $pua = $ps->totalpointsunanswered;

                // get the toal points available
                $pav = $ps->totalpointsavailable;

                // get the total points wrong answered
                $pwa = $pav - $pac - $pua;

                // convert each of the scores
                $maxScore = $ps->getMarkForPoints($pav, $pav, $nterm);
                $pacScore = $ps->getMarkForPoints($pac, $pav, $nterm);
                $pwaScore = $ps->getMarkForPoints($pac + $pwa, $pav, $nterm);

                // get the score per points
                $ps->totalpointsavailable = $maxScore;
                $ps->pointsAchieved = $pacScore;
                $ps->pointsWrongAnswered = round($pwaScore - $pacScore, 1);
                $ps->pointsUnanswered = round($maxScore - $pacScore - $ps->pointsWrongAnswered, 1);

            }

            //$pacdata = $practicesessions->pluck('scoreAchieved');
            //$pwadata = $practicesessions->pluck('pointsWrongAnswered');
            //$puadata = $practicesessions->pluck('scoreUnanswered');
            //$scoreMax = $practicesessions->first()->scoreMax;
        }
        else {

            //$pacdata = $practicesessions->pluck('pointsAchieved');
            //$pwadata = $practicesessions->pluck('pointsWrongAnswered');
            //$puadata = $practicesessions->pluck('pointsUnanswered');
            //$scoreMax = $practicesessions->first()->totalpointsavailable;
        }

        return [
            'valid' => true,
            'updateElement' => [
                $element => $this->renderPartial('examifyHelpers/portal/charts/pointsPerStudent', [
                    'practicesessions' => $practicesessions->sortBy('surname')->values()->all()
                ])
            ]
        ];
    }

    function onGetProgressPerStudent()
    {

        // get the homework id
        $this->validateMe();

        // get all the practicesessions belonging to this homework
        $practicesessions = $this->homework->getPracticeSessionsWithStudentNameAndProgress()->where('finished', false);

        // order it by student name
        if(!$practicesessions->count())
        {
            return [
                'valid' => false,
                'showElement' => [
                    '#placeholder-chart-progress'
                ],
                'hideElement' => [
                    '#studentProgressChart'
                ]
            ];
        }

        // sort them
        $practicesessions = $practicesessions->sortBy('surname');

        // in case of superadmin, random names
        if($this->user->getUserSetting('RandomNames'))
        {
            $labels = [];
            foreach($practicesessions as $i)
            {
                $labels[] = Str::random(10);
            }
        }
        else {
            $labels = $practicesessions->pluck('studentname');
        }

        return [
            'valid' => true,
            'extra' => $practicesessions->values(),
            'chartHeight' => 36 * $practicesessions->count() + 60,
            'chartSetup' => [
                'type' => 'horizontalBar',
                'legend' => [
                    'display' => false
                ],
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Aantal vragen afgerond',
                            'data' => $practicesessions->pluck('nansweredquestions'),
                            'backgroundColor' => '#5282ca'
                        ],
                        [
                            'label' => 'Aantal vragen te doen',
                            'data' => $practicesessions->pluck('nunansweredquestions'),
                            'backgroundColor' => '#ddd'
                        ]
                    ]
                ],
                'options' => [
                    'animation' => [
                        'duration' => 2000
                    ],
                    'maintainAspectRatio' => false,
                    'scales' => [
                        'xAxes' => [[
                            'stacked' => true,
                            'ticks' => [
                                'beginAtZero' => true,
                                'min' => 0,
                                'max' => $this->homework->nquestions
                            ]
                        ]],
                        'yAxes' => [[
                            'stacked' => true
                        ]]
                    ]
                ]
            ]
        ];
    }

    // get all the logs belonging to this homework
    function getAllLogs()
    {

        $homework = $this->homework;

        // get all the practice sessions ids
        $psids = $homework->practiceSessions->pluck('id');

        return $psids;

        // get the logs belonging to this homework
        return PracticeSessionsResultsLogs::whereIn('practice_session_id', $psids->values()->all())->get();

    }

}
