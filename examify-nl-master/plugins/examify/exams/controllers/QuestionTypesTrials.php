<?php namespace Examify\Exams\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class QuestionTypesTrials extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController',        'Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'exams.exams' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Examify.Exams', 'main-menu-item', 'side-menu-item6');
    }
}
