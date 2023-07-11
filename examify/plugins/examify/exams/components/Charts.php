<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;

use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\PracticeSessionsResultsLogs as PracticeSessionsResultsLogs;
use Examify\Exams\Models\Questions as Questions;
use Examify\Exams\Models\PracticeSessions as PS;
use Examify\Exams\Models\QuestionsAnswersLogs as QAL;
use Examify\Exams\Models\Answers as Answers;

class Charts extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Charts Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }


    public function checkUserPracticeSessionInput()
    {
        // get the practice session
        if(!$psid = input('psid', false)){
            return [
                'valid' => false
            ];
        }

        // check if a user is defined
        if(!$user = Users::getUser()){
            return [
                'valid' => false
            ];
        }

        // check if this user has this practice session
        if(!$myPracticeSession = $user->hasPracticeSession($psid)){
            return [
                'valid' => false,
                'details' => 'Deze oefensessie kunnen we niet terugvinden in ons systeem.'
            ];
        }

        if(!$myPracticeSession->finished){
            return [
                'valid' => false,
                'details' => 'Deze oefensessie is nog niet afgerond en er zijn dus nog geen statistieken beschikbaar.'
            ];
        }

        return [
            'valid' => true,
            'PracticeSession' => $myPracticeSession,
            'User' => $user
        ];
    }

    public static function getPercentageColor($myPercentage)
    {
        if($myPercentage > 75){
            return '#0e8553';
        }
        
        if($myPercentage > 55){
            return '#81b437';
        }
        if($myPercentage > 45){
            return '#fdbf12';
        }
        if($myPercentage > 35){
            return '#f0841d';
        }
        if($myPercentage > 25){
            return '#e95721';
        }
        
        return '#e02022';

    }

    public static function getMarkColor($myPoint, $maxPoints)
    {

        // make it relative up to scale of 10
        $myPoint = $myPoint / $maxPoints * 10;

        if($myPoint >= 8){
            return '#0e8553';
        }
        
        if($myPoint >= 6){
            return '#9ace97';
        }
        if($myPoint >= 5){
            return '#fdbf12';
        }
        if($myPoint >= 4){
            return '#f0841d';
        }
        if($myPoint >= 2){
            return '#e95721';
        }
        
        return '#e02022';
    }

    public function generateCircleData($value, $type, $maxValue = 10, $nDecimals = 1)
    {

        switch ($type) {
            case 'percentage':
                # code...
                $myValue = $value / 100;
                $color = Charts::getPercentageColor($value);
                break;

            case 'points':
                # code...
                $myValue = $value / $maxValue;
                $color = Charts::getMarkColor($value, $maxValue);
                break;
            
            default:
                return [
                    'valid' => false,
                    'details' => 'Type is not supported ' . $type
                ];
                break;
        }

        // get the chart delay if defined
        $chartDelay = input('delay', 0);

        return [
            'valid' => true,
            'chartSetup' => [
                'color' => '#aaa',
                'strokeWidth' => 4,
                'trailWidth' => 4,
                'easing' => 'easeInOut',
                'duration' => 1400,
                'text' => [
                    'autoStyleContainer' => false
                ],
            ],
            'chartDelay' => $chartDelay,
            'chartMax' => $maxValue,
            'chartNdecimals' => $nDecimals,
            'value' => $myValue,
            'chartOptions' => [
                'from' => [
                    'color' => '#e02022',
                    'width' => 1
                ],
                'to' => [
                    'color' => $color,
                    'width' => 5
                ]
            ],
            'chartUnit' => $type
        ];
    }

    public function getChartDataPointsAchieved($allLogs)
    {

        // set the value
        $pointsAchieved = $allLogs->sum('points_achieved');
        $pointsAvailable = $allLogs->sum('points_available');

        return $this->generateCircleData($pointsAchieved, 'points', $pointsAvailable, 0);

    }

    public function onGetPracticeSessionQuestionsAnswered()
    {

        // check that all inputs are there
        $myInput = $this->checkUserPracticeSessionInput();

        if(!$myInput['valid']){
            return $myInput;
        }

        $myPracticeSession = $myInput['PracticeSession'];

        // get the percentage of answered questions
        $myPercentage = $myPracticeSession->getPercentageAnswered();

        return $this->generateCircleData($myPercentage['value'], 'points', $myPercentage['max'], 0);

    }

    public function onGetPracticeSessionDataQuestionTypes()
    {
        // check that all inputs are there
        $myInput = $this->checkUserPracticeSessionInput();

        if(!$myInput['valid']){
            return $myInput;
        }

        $myPracticeSession = $myInput['PracticeSession'];

        // check if this practice sessions has childs
        if($myPracticeSession->child_ids){
            $scaling = count($myPracticeSession->child_ids);
        }
        else {
            $scaling = 1;
        }

        // get all the relevant logs for this session
        $cp = collect($myPracticeSession->cached_points);
        $logids = $cp->pluck('log_id');

        $allLogs = PracticeSessionsResultsLogs::with('questionType')->find($logids);
        
        // now get the relevant data for this session
        //$allLogs = $myPracticeSession->resultsLogs()->with('questionType')->get();

        // return the data 
        return $this->getChartDataQuestionTypes($allLogs, 'Goed', 'Fout', 'Onbeantwoord', $scaling);
    }

    public function onGetPracticeSessionDataFinalMark()
    {

        // check that all inputs are there
        $myInput = $this->checkUserPracticeSessionInput();

        if(!$myInput['valid']){
            return $myInput;
        }

        $myPracticeSession = $myInput['PracticeSession'];

        // check that it is in examenmodus
        if(!$exam_id = $myPracticeSession->exam_id){
            // get the percentage
            $percentage = round($myPracticeSession->totalpointsachieved / $myPracticeSession->totalpointsavailable * 100);

            return $this->generateCircleData($percentage, 'percentage', 100, 0);
        }

        // get the mark
        return $this->generateCircleData($myPracticeSession->mark, 'points', 10);

    }

    public function onGetPracticeSessionDataPointsAchieved()
    {

        // check that all inputs are there
        $myInput = $this->checkUserPracticeSessionInput();

        if(!$myInput['valid']){
            return $myInput;
        }

        $myps = $myInput['PracticeSession'];

        return $this->generateCircleData($myps->totalpointsachieved, 'points', $myps->totalpointsavailable, 0);
        
    }

    public function getChartDataQuestionTypes($allLogs, $legendGood = 'Punten behaald', $legendWrong = 'Punten foutief beantwoord', $legendUnanswered = 'Punten onbeantwoord', $scaling = 1)
    {

        // get all the question types
        $questionTypes = $allLogs->pluck('questionType')->unique('id');

        // get the number of practicesessions to average over
        $npracticesessions = $allLogs->pluck('practice_session_id')->unique()->count() / $scaling;

        // store the data
        $backgroundColor = array();
        $pointsAchieved = array();
        $pointsAvailable = array();
        $pointsWrongAnswered = array();
        $pointsUnanswered = array();
        $labels = array();
        $borderColor = array();

        $myCollection = collect();

        $myScore = 0;

        // now walk over the question types and get the score per question type
        foreach($questionTypes as $qType)
        {

            // add the score to the dataset
            $subQuestions = $allLogs->where('questionType.id', $qType->id);
            
            $pointsAchieved = round($subQuestions->sum('points_achieved') / $npracticesessions, 2);
            $pointsAvailable = round($subQuestions->sum('points_available') / $npracticesessions, 2);
            $pointsUnanswered = round($subQuestions->sum('points_unanswered') / $npracticesessions, 2);


            // create a new collection
            $myCollection->push(
                [
                    'label' => $qType->name,
                    'pointsAchieved' => $pointsAchieved,
                    'pointsWrongAnswered' => $pointsAvailable - $pointsAchieved - $pointsUnanswered,
                    'pointsAvailable' => $pointsAvailable,
                    'pointsUnanswered' => $pointsUnanswered
                ]
            );

        }

        // update the others based on the keys
        $sortedCollection = $myCollection->sortByDesc('pointsAvailable');


        return [
            'valid' => true,
            'extra' => $sortedCollection->values(),
            'chartHeight' => 36 * $sortedCollection->count() + 60,
            'chartSetup' => [
                'type' => 'horizontalBar',
                'legend' => [
                    'display' => false
                ],
                'data' => [
                    'labels' => $sortedCollection->pluck('label'),
                    'datasets' => [
                        [
                            'label' => $legendGood,
                            'data' => $sortedCollection->pluck('pointsAchieved'),
                            'backgroundColor' => '#28a745'
                        ],
                        [
                            'label' => $legendWrong,
                            'data' => $sortedCollection->pluck('pointsWrongAnswered'),
                            'backgroundColor' => '#ddd'
                        ],
                        [
                            'label' => $legendUnanswered,
                            'data' => $sortedCollection->pluck('pointsUnanswered'),
                            'backgroundColor' => '#aaa'
                        ]
                    ]
                ],
                'options' => [
                    'animation' => [
                        'duration' => 4000
                    ],
                    'maintainAspectRatio' => false,
                    'scales' => [
                        'xAxes' => [[
                            'stacked' => true,
                            'ticks' => [
                                'beginAtZero' => true
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

    public function getChartDataAnswerTypes($allLogs)
    {

        // get all the question types
        $questionIDs = $allLogs->pluck('question_id');
        $questions = Questions::find($questionIDs);
        $answerTypes = $questions->pluck('answer_type')->unique();

        // get all the unique practice sessions
        $npracticesessions = $allLogs->pluck('practice_session_id')->unique()->count();

        // get all relati

        // store the data
        $backgroundColor = array();
        $pointsAchieved = array();
        $pointsAvailable = array();
        $pointsWrongAnswered = array();
        $pointsUnanswered = array();
        $labels = array();
        $borderColor = array();

        $myCollection = collect();

        $myScore = 0;

        // now walk over the question types and get the score per question type
        foreach($answerTypes as $aType)
        {

            // find the question ids with this answer type
            $subQuestionIDs = $questions->where('answer_type', $aType)->pluck('id')->values();

            // add the score to the dataset
            $subQuestions = $allLogs->whereIn('question_id', $subQuestionIDs);
            
            $pointsAchieved = $subQuestions->sum('points_achieved') / $npracticesessions;
            $pointsAvailable = $subQuestions->sum('points_available') / $npracticesessions;
            $pointsUnanswered = $subQuestions->sum('points_unanswered') / $npracticesessions;


            // create a new collection
            $myCollection->push(
                [
                    'label' => $aType,
                    'pointsAchieved' => $pointsAchieved,
                    'pointsWrongAnswered' => $pointsAvailable - $pointsAchieved - $pointsUnanswered,
                    'pointsAvailable' => $pointsAvailable,
                    'pointsUnanswered' => $pointsUnanswered
                ]
            );

        }

        // update the others based on the keys
        $sortedCollection = $myCollection->sortByDesc('pointsAvailable');


        return [
            'valid' => true,
            'extra' => $sortedCollection->values(),
            'chartHeight' => 40 * $sortedCollection->count(),
            'chartSetup' => [
                'type' => 'horizontalBar',
                'legend' => [
                    'display' => false
                ],
                'data' => [
                    'labels' => $sortedCollection->pluck('label'),
                    'datasets' => [
                        [
                            'label' => 'Punten behaald',
                            'data' => $sortedCollection->pluck('pointsAchieved'),
                            'backgroundColor' => '#28a745'
                        ],
                        [
                            'label' => 'Punten foutief beantwoord',
                            'data' => $sortedCollection->pluck('pointsWrongAnswered'),
                            'backgroundColor' => '#ddd'
                        ],
                        [
                            'label' => 'Punten onbeantwoord',
                            'data' => $sortedCollection->pluck('pointsUnanswered'),
                            'backgroundColor' => '#aaa'
                        ]
                    ]
                ],
                'options' => [
                    'animation' => [
                        'duration' => 4000
                    ],
                    'maintainAspectRatio' => false,
                    'scales' => [
                        'xAxes' => [[
                            'stacked' => true,
                            'ticks' => [
                                'beginAtZero' => true
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

    public function onGetPracticeSessionTimeline()
    {
        // get the practice session id and validate the user can see it
        $user = Users::getUser();
        if(!$user){ return; }

        $ps = PS::find(input('psid', 0));
        if(!$ps){ return; }

        if(!$ps->isVisibleForUser($user)){ return; }

        // get the logs for this session
        //$qals = QAL::whereRaw('(open_answer IS NOT NULL OR answer_id IS NOT NULL) AND practice_session_id = ?', $ps->id)->with('question')->orderBy('created_at')->get();
        $qals = $ps->getQuestionsAnswersLogsWithQuestions();
        $timestamps = $qals->pluck('created_at')->values();
        $ydata = $qals->pluck('question.question_nr')->values();

        $datagood = [];
        $databad = [];
        $datachanged = [];
        foreach($qals as $qal)
        {
            $x = (int) round($qal->created_at->format('Uu') / pow(10, 6 - 3));
            $y = $qal->question->question_nr;
            $dataelement = [
                'x' => $x,
                'y' => $y
            ]; 

            if($qal->is_final){

                if($qal->answer_id > 0){
                    $a = Answers::find($qal->answer_id);
                    if($a->points == $qal->points){
                        $datagood[] = $dataelement;
                    }
                    else {
                        $databad[] = $dataelement;
                    }
                }
                else {
                    if($qal->points > 0){ $datagood[] = $dataelement; }
                    else { $databad[] = $dataelement; }
                }

            }
            else { $datachanged[] = $dataelement; }

        }

        return [
            'valid' => true,
            'ydata' => $ydata,
            //'chartHeight' =>
            'chartSetup' => [
                'type' => 'scatter',
                'legend' => [
                    'display' => false,
                ],
                'labels' => 'Tijdsbesteding',
                'data' => [
                    'datasets' => [
                        [
                            'label' => 'Goed antwoord',
                            'fill' => false,
                            'data' => $datagood,
                            'pointRadius' => 10,
                            'pointHoverRadius' => 20,
                            'backgroundColor' => 'rgba(40,167,69,0.5)'
                        ],
                        [
                            'label' => 'Fout antwoord',
                            'fill' => false,
                            'data' => $databad,
                            'pointRadius' => 10,
                            'pointHoverRadius' => 20,
                            'backgroundColor' => 'rgba(255,0,0,0.5)'
                        ],
                        [
                            'label' => 'Later veranderd',
                            'fill' => false,
                            'data' => $datachanged,
                            'pointRadius' => 10,
                            'pointHoverRadius' => 20,
                            'backgroundColor' => 'rgba(82,130,202,0.5)'
                        ]
                    ]
                ],
                'options' => [
                    'scales' => [
                        'yAxes' => [[
                            'scaleLabel' => [
                                'display' => true,
                                'labelString' => 'Vraagnummer'
                            ]
                        ]],
                        'xAxes' => [[
                            'scaleLabel' => [
                                'display' => true,
                                'labelString' => 'Tijd'
                            ],
                            'type' => 'time',
                            'time' => [
                                'tooltipFormat' => 'DD-MM-YYYY // HH:mm:ss',
                                'displayFormats' => [
                                    'millisecond' => 'HH:mm:ss',
                                    'second' => 'HH:mm:ss',
                                    'minute' => 'HH:mm',
                                    'hour' => 'DD-MM HH:mm',
                                    'day' => 'DD-MM HH:mm',
                                    'week' => 'DD-MM HH:mm',
                                    'month' => 'DD-MM HH:mm',
                                    'quarter' => 'DD-MM HH:mm',
                                    'year' => 'DD-MM HH:mm',
                                ]
                            ],
                        ]],
                    ]
                ]
            ]
        ];


    }
}
