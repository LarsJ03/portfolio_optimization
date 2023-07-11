<?php namespace Examify\Exams\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Classes extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'exams.exams' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Examify.Exams', 'main-menu-item', 'schoolclass');
    }
}
