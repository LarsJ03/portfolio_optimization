<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Examify\Exams\Models\Exams as Exams;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\Courses as Courses;
use Examify\Exams\Models\PracticeSessions as PracticeSessions;

class PracticeSessionSelector extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'PracticeSessionSelector Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        // add the Javascripts
        //$this->page->addJs('assets/js/practice-session-selector.js');

        // get the user
        if(Auth::check()){

            // get the user
            $this->page['user'] = $user = Users::getUser();

            // get the courses for which this user is licensed
            $this->page['lics']     = $user->getLicensedCoursesForYearAndLevel();
            $this->page['ulics']    = $user->getUnlicensedCoursesForYearAndLevel();

            // get the practice sessions
            $practiceSessions = $user->practiceSessions()->get();

            // loop over them to update the cache
            foreach($practiceSessions as $ps){
                $ps->generateCachedAnswersProperty(true);
                $ps->generateCachedPointsProperty(true);
            }

            if(!$practiceSessions->count())
            {
                $this->page['hasPracticeSessions'] = false;
                return;
            }

            $this->page['hasPracticeSessions'] = true;

            $sessionsPerCourse = $practiceSessions->groupBy('course_id');
            $this->page['practiceSessionsPerCourse'] = $sessionsPerCourse;

        }

    }
}
