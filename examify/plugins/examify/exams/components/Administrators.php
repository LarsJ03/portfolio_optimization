<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use \Examify\Exams\Models\Users as Users;

class Administrators extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Administrators Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {

        // check if the user is an administrator
        $User = Users::getUser();
        $User->isAdmin = $User->getUserSetting('isAdmin');
        $User->isSuperAdmin = $User->getUserSetting('isSuperAdmin');

        $this->page['user'] = $User;

        if(!$User->isAdmin){
            return;
        }

        // get all the administrators
        $this->page['MyAdmins'] = $this->getAdministrators();
        $this->page['AllUsers'] = Users::all();
    }

    // get all the administrators
    public function getAdministrators()
    {
        $allUsers = Users::all()->sortBy('name');
        $myResultArray = [];

        foreach($allUsers as $user){
            if($user->getUserSetting('isAdmin') == true)
            {
                $myResultArray[] = $user;
            }
        }

        return $myResultArray;
    }

    public function onSubmit()
    {

        // get the current user and check if it is admin
        $user = Users::getUser();

        if(!$user || !$user->getUserSetting('isAdmin')){
            return [
                'valid' => false
            ];
        }

        // get the user id
        $user_id = input('user_id');

        if(!$user_id)
        {
            return [
                'valid' => false,
                'message' => 'Selecteer een gebruiker.'
            ];
        }


        $updateUser = Users::find($user_id);
        $updateUser->setUserSetting('isAdmin', true);

        // get the active input
        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-administrators' => $this->renderPartial('examifyHelpers/portal/listOfAdministrators' , 
                        [
                            'MyAdmins' => $this->getAdministrators(),
                            'user' => $user,
                            'isSuperAdmin' => $user->getUserSetting('isSuperAdmin')
                        ])
            ]
        ];
        
    }

    public function onDelete()
    {

        // get the current user and check if it is admin
        $user = Users::getUser();

        if(!$user || !$user->getUserSetting('isAdmin')){
            return [
                'valid' => false
            ];
        }

        // get the extraData
        $extraData = input('extraData', false);

        if(!$extraData){
            return [
                'valid' => false
            ];
        }

        $extraData = json_decode($extraData, true);

        $user_id = $extraData['user_id'];

        // do not do anything if the current user is selected
        if($user_id == $user->id){
            return;
        }

        $updateUser = Users::find($user_id);
        $updateUser->setUserSetting('isAdmin', false);

        // get the active input
        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-administrators' => $this->renderPartial('examifyHelpers/portal/listOfAdministrators' , 
                    [
                        'MyAdmins' => $this->getAdministrators(),
                        'user' => $user,
                        'isSuperAdmin' => $user->getUserSetting('isSuperAdmin')
                    ])
            ]
        ];
        
    }
}
