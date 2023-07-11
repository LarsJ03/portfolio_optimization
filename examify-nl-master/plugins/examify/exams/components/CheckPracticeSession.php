<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\PracticeSessions as PracticeSessions;
use Examify\Exams\Models\QuestionsAnswersLogs as QuestionsAnswersLogs;
use Examify\Exams\Models\Questions as Questions;
use Examify\Exams\Models\Texts as Texts;
use Examify\Exams\Models\QuestionsLocks as QuestionsLocks;

class CheckPracticeSession extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'CheckPracticeSession Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    function onSubmitOpenAnswerPoints()
    {

        // set the textMessage in case not all answers are rewarded
        $textMessage = 'Je moet bij alle vragen aangeven hoeveel punten je hebt verdiend. Als je geen punten verdiend hebt, selecteer dan 0 punten.';

        if(!($checkQuestions = input('checkQuestions', false))
            || !($questionIdentifier = input('questionIdentifier', false))
            || (count($checkQuestions) != count($questionIdentifier))
            || !($psid = input('practice_session_id', false))
        ) {

            // return that not all questions are checked
            return [
                'valid' => false,
                'message' => $textMessage
            ];
        }

        if(!$user = Users::getUser()){
            return [
                'valid' => false,
                'message' => 'Je bent niet ingelogd!'
            ];
        }

        // check if this practice session belongs to this user
        if(!$thisPracticeSession = PracticeSessions::where('id', $psid)
                                                ->where('user_id', $user->id)
                                                ->where('finished', false)
                                                ->first()){

            return [
                'valid' => false,
                'message' => 'Deze oefensessie is niet gevonden in ons systeem.'
            ];
        }

        // get all the questions
        $myQuestionIDs = array_keys($checkQuestions);
        $myQuestions = Questions::find($myQuestionIDs);

        // now for each question, get the cached answer 
        foreach($myQuestions as $thisQuestion)
        {
            // get the answer
            if(!($myAnswer = QuestionsAnswersLogs::getCachedAnswer($thisQuestion, $thisPracticeSession->id))
                || !($logObject = $myAnswer['logobject'])
            ){

                return [
                    'valid' => false,
                    'message' => 'Er is iets fout gegaan met het verwerken van de toegekende punten.'
                ];
            }

            // add a validation that the points assigned by the user do not exceed the maximum number of points of this question, and also do not go lower than 0.
            $pointsAssigned = $checkQuestions[$thisQuestion->id];
            $pointsAssigned = min($thisQuestion->points, $pointsAssigned);
            $pointsAssigned = max($pointsAssigned, 0);


            // update the points in the log object
            $logObject->points = $pointsAssigned;
            $logObject->save();

        }

        // finish the practice session
        return $thisPracticeSession->finish($this);

    }

    function onSubmitPracticeSession()
    {

        $extraData = input('extraData', false);

        // create empty array if it is not provided
        if(!$extraData)
        {
            $extraData = array();
        }
        else {
            $extraData = json_decode($extraData, true); // convert to arrays
        }

        // in case continue with practice session is submitted, do that
        if(array_key_exists('continuePracticeSession', $extraData) && $extraData['continuePracticeSession'] == true){
            return [
                'valid' => false,
                'showWithinForm' => [
                    'button.hand-in-button'
                ],
                'updateWithinForm' => [
                    '.info-feedback' => ''
                ],
                'hideWithinForm' => [
                    '.info-feedback'
                ]
            ];
        }

        // check if it is forced handin
        $forceHandIn = array_key_exists('forceHandIn', $extraData) ? $extraData['forceHandIn'] : false;

        // check if there is a user
        if(!$user = Users::getUser()){
            return [
                'valid' => false,
                'message' => 'Log in!'
            ];
        }

        // check if this practice session is a valid one for this user
        $psid = input('psid');

        // get the active practice session
        $myPracticeSession = $user->hasActivePracticeSession($psid);
        if(!$myPracticeSession){
            return [
                'valid' => false,
                'message' => 'Deze oefensessie is al nagekeken!'
            ];
        }

        // check if there are questions that are unanswered
        $unansweredQuestions = $myPracticeSession->getUnansweredQuestions();
        if(!($myPracticeSession->leermodus) && (!($unansweredQuestions) || $forceHandIn)){

            // finish the practice session, also put as argument the controller such that the partials can be rendered
            return $myPracticeSession->finish($this);

        }

        // in case of a leermodus, check if the unanswered questions are in line with the tracker_examify_pagination_active_text_id
        $textQuestionIDs = [];
        if($myPracticeSession->leermodus){
            $tracker    = input('active_exam_text_id');
            $activePage = input('tracker_examify_pagination_active_view');

            // in case there are in general no unanswered questions anymore for the leermodus, set it to finished in the practice session as well
            if(!$unansweredQuestions || !$unansweredQuestions->count()){
                $myPracticeSession->finished = true;
                $myPracticeSession->save();
            }

            // remove questions that are not belonging to this text
            $thisText = Texts::with('questions')->find($tracker);

            if($thisText !== null){
                // get the question ids
                $textQuestionIDs = $thisText->questions->pluck('id');

                // the unansweredQuestions should be filtered with this one
                if($unansweredQuestions)
                {
                    $unansweredQuestions = $unansweredQuestions->whereIn('id', $textQuestionIDs);
                }

                // if there are no unanswered questions anymore, or if there is a forceHandIn, save the questions belonging to this text as locked
                if(!$unansweredQuestions || (!$unansweredQuestions->count()) || $forceHandIn){

                    $questionsToLock = $myPracticeSession->getQuestionsByText($thisText);



                    // get the questions belonging to this text
                    if($questionsToLock !== null){

                        $ql = collect($myPracticeSession->questions_locked);
                        foreach($questionsToLock as $qq){
                            $ql->prepend($qq->id);
                        }

                        $myPracticeSession->update(['questions_locked' => $ql]);

                        // now regenerate
                        return [
                            'valid' => true,
                            'updateElement' => [
                                '#placeholder-full-session' => $this->renderPartial('examifyHelpers/fullExamScroll', ['practiceSession' => $myPracticeSession, 'activePageToSet' => $activePage])
                            ],
                            'call-js-function' => [
                                'iniExamifyQuestions' => false,
                                'iniPagination' => false,
                                'iniAllChartForms' => false,
                                'iniAllProgressBarForms' => false,
                                'alignFooter' => false,
                            ],
                            'scrollToElement' => '#placeholder-full-session'
                        ];
                    }
                }

            }
        }

        return [
            'valid' => false,
            'updateWithinForm' => [],
            'hideWithinForm' => [
                'button.hand-in-button'
            ],
            'showWithinForm' => [],
            'message-info' => $this->renderPartial('examifyHelpers/checkPracticeSessionIncomplete', [
                'leermodus' => $myPracticeSession->leermodus
            ])
        ];
    }
}
