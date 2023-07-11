<?php namespace Examify\Exams\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Examify\Exams\Models\Exams as Exams;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\PracticeSessions as PS;
use Examify\Exams\Models\QuestionTypesTrials as Trials;
use Examify\Exams\Models\Classes as Classes;
use Examify\Exams\Models\Questions as Questions;

class PracticeSessions extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController'    ];
    
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = [
        'exams.exams' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Examify.Exams', 'main-menu-item2');
    }

    public function new()
    {
        if(!$user = Users::getUser()){
            return redirect('/login');
        }

        $exam_id = input('exam_id', 0);
        $leermodus = input('leermodus', 0);

        // find the exam
        $exam = Exams::where('visible', true)->find($exam_id);
        if(!$exam){ 
            return 'Dit examen is niet gevonden in ons systeem.';
        }

        $myExam = Exams::where('id', $exam_id)->availableForUser()->with('texts.questions', 'course')->first();

        if(!$myExam){
            return 'Je hebt geen licentie voor dit vak. Je kunt op de homepage licenties aanschaffen!';
        }   

        $leermodus = !!$leermodus;

        if($leermodus && !$exam->practice_mode_available){
            return 'Dit examen kan nog niet in leermodus geoefend worden.';
        }

        // check the exam texts
        $examTexts = $myExam->texts;
        if(!$examTexts->count()){
            return 'Dit examen heeft nog geen teksten die eraan gekoppeld zijn.';
        }

        // error in case there are no questions
        $textQuestions = $examTexts->pluck('questions')->flatten();

        if(!$textQuestions->count()){
            return 'Er zijn nog geen vragen gekoppeld aan dit examen.';
        }

        $questionIDs = $textQuestions->pluck('id');
        $availablePoints = $textQuestions->sum('points');

        // create the new session
        $ps = new PS();

        $ps->user_id = $user->id;
        $ps->exam_id = $myExam->id;
        $ps->course_id = $myExam->course->id;
        $ps->leermodus = $leermodus;
        $ps->finished = false;
        $ps->name = 'Oefensessie';
        $ps->question_id = $questionIDs;
        $ps->totalpointsavailable = $availablePoints;
        $ps->save();

        return redirect('/oefenen/mijn-oefensessie-' . $ps->id);

    }

    public function new_trial()
    {

        if(!$user = Users::getUser()){
            return redirect('/login');
        }

        $trial_id = input('trial_id', 0);

        $trial = Trials::where('visible', true)->find($trial_id);
        if(!$trial){
            return 'Deze vraagtype-oefensessie is niet gevonden in ons systeem.';
        }

        $year = Classes::getCurrentYear();

        // check if the user has a license for this year and this course
        if(!$user->hasLicenceForCourseIdAndYear($trial->course_id, $year)){
            return 'Je hebt geen licentie voor dit vak voor dit schooljaar.';
        }

        $leermodus = true; // always do trials in leermodus

        // get the questions coupled to this trial
        $question_ids = collect($trial->question_ids);
        if(empty($question_ids)){
            return 'Er zijn nog geen vragen gekoppeld aan deze vraagtype oefensessie.';
        }

        // get the questions
        $questions = Questions::find($question_ids);

        $questionIDs = $questions->pluck('id');
        $availablePoints = $questions->sum('points');

        $ps = new PS();
        $ps->user_id = $user->id;
        $ps->exam_id = 0;
        $ps->course_id = $trial->course_id;
        $ps->leermodus = $leermodus;
        $ps->finished = false;
        $ps->name = 'Vraagtype oefensessie';
        $ps->question_id = $questionIDs;
        $ps->totalpointsavailable = $availablePoints;
        $ps->question_types_trials_id = $trial->id;
        $ps->save();

        return redirect('/oefenen/mijn-oefensessie-' . $ps->id);


    }
}
