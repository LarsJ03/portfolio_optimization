<?php namespace Examify\Exams\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Licenses extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController', 
        'Backend.Behaviors.ImportExportController'
    ];

    public $importExportConfig = 'config_import_export.yaml';
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'exams.exams' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Examify.Exams', 'main-menu-item', 'licenses');
    }
}
