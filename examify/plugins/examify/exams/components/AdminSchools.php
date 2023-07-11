<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use \Examify\Exams\Models\Schools as Schools;
use \Examify\Exams\Models\Users as Users;

class AdminSchools extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AdminSchools Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    function onRun()
    {

        // check if the user is an administrator
        $user = Users::getUser();
        $user->isAdmin = $user->getUserSetting('isAdmin');
        $user->isSuperAdmin = $user->getUserSetting('isSuperAdmin');

        $this->page['user'] = $user;

        if(!$user->isAdmin){
            return;
        }

        // get all the schools
        $this->page['MySchools'] = $this->getSchools();

        $this->page['schoolyear'] = $schoolyear = $this->param('year');

    }

    function getSchools()
    {
        return Schools::all()->sortBy('name')->sortBy('place');
    }

    function onAdd()
    {

        $schoolname = input('schoolname');
        $place = input('place');

        if(!$schoolname || strlen($schoolname) < 3){
            return [
                'valid' => false,
                'message' => 'De schoolnaam moet minstens 3 characters lang zijn. (' . input('schoolname') . ')'
            ];
        }

        if(!$place)
        {
            return [
                'valid' => false,
                'message' => 'De plaats moet ingevuld zijn.'
            ];
        }

        // check if this school already exists
        $alreadyExists = Schools::where('name', $schoolname)->where('place', $place)->get()->count();

        if($alreadyExists > 0){
            return [
                'valid' => false,
                'message' => 'Deze school in deze plaats is al eerder toegevoegd in het systeem.'
            ];
        }

        // add the school
        $newSchool = new Schools();
        $newSchool->name = $schoolname;
        $newSchool->place = $place;
        $newSchool->save();

        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-schools' => $this->renderPartial('examifyHelpers/portal/listOfSchools', ['MySchools' => $this->getSchools()])
            ]
        ];
        
    }
}
