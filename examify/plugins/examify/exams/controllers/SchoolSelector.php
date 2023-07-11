<?php namespace Examify\Exams\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Examify\Exams\Models\Users as Users;

/**
 * School Selector Backend Controller
 */
class SchoolSelector extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Examify.Exams', 'exams', 'schoolselector');
    }

    public function change($schoolid)
    {
        if(!$user = Users::getUser()){
            return redirect('/login');
        }

        if(!$user->isSuperAdmin()){
            return redirect('/login');
        }

        $user->setUserSetting('schoolid', $schoolid);

        return redirect('/portal/classes');

    }
}
