<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use \Examify\Exams\Components\Charts as ChartsBase;
use \Examify\Exams\Models\Users as Users;
use \Examify\Exams\Models\Schools as Schools;
use \Examify\Exams\Models\PracticeSessionsResultsLogs as PracticeSessionsResultsLogs;

class Statistics extends ChartsBase
{

    function onGetStatisticsPerQuestionType()
    {

        // get all the logs
        $allLogs = $this->onGetAllLogs();

        if(empty($allLogs))
        {
            return;
        }

        // get the chartdata
        return $this->getChartDataQuestionTypes($allLogs);

    }

    function onGetStatisticsPerAnswerType()
    {

        // get all the logs
        $allLogs = $this->onGetAllLogs();

        if(empty($allLogs))
        {
            return;
        }

        return $this->getChartDataAnswerTypes($allLogs);

    }
    
    function onGetAllLogs()
    {

        // get all available logs for this user
        // this is related to the students that are in the class of the teacher, 

        // get the current user
        $user = Users::getUser();

        if(!$user)
        {
            return [];
        }

        // in case the user is a superuser
        $user->isSuperAdmin = $user->getUserSetting('isSuperAdmin') ? true : false;

        if($user->isSuperAdmin)
        {
            $schools = Schools::with('classes', 'classes.course', 'classes.students')->get();
        }
        else {
            // in case the user is a school admin, he can see all classes of its school
            $schools = $this->schools()->wherePivot('is_school_admin', true)->with('classes', 'classes.course', 'classes.students')->get();
        }

        if(!$schools->count())
        {
            // the classes are bounded to where the user is teacher for
            $classes = $user->classes()->with('course', 'students')->wherePivot('is_teacher', true)->get();
        }
        else {

            // the user has schools assigned where it is the admin for, the classes are all classes assigned to this school
            $classes = $schools->pluck('classes')->flatten();

        }

        // now get all the courses
        if(!$classes->count())
        {
            return [];
        }
        // construct the query
        $myQuery = PracticeSessionsResultsLogs::query();

        foreach($classes as $class)
        {
            $myQuery->orWhere(function ($query) use ($class){
                $query->whereIn('user_id', $class->students->pluck('id'))
                        ->where('course_id', $class->course_id);
            });
        }

        $myLogs = $myQuery->get();

        return $myLogs;

    }

}
