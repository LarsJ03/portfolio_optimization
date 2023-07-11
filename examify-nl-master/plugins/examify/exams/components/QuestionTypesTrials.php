<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use Examify\Exams\Models\Users as Users;
use Examify\Exams\Models\Courses as Courses;

/**
 * QuestionTypesTrials Component
 */
class QuestionTypesTrials extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'QuestionTypesTrials Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $user = Users::getUser();

        if(!$user){ return; }

        // only get the courses for which this user has the same level of education
        $level = $user->getUserSetting('level');
        if(!$level){ return; }

        // get the courses with the same level
        $courses = Courses::where('level', $level)->get();

        $this->page['courses_for_trials'] = $courses;

    }
}
