<?php namespace Examify\Exams;

use System\Classes\PluginBase;
use Event;
use Examify\Exams\Models\Users as Users;
use Yaml;
use Illuminate\Database\DatabaseManager;
use App;
use Config;
use Illuminate\Foundation\AliasLoader;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    	return [
 				'Examify\Exams\Components\Question' => 'ExamifyQuestion',
 				'Examify\Exams\Components\Account' => 'ExamifyAccount',
 				'Examify\Exams\Components\PracticeSessionSelector' => 'ExamifyPracticeSessionSelector',
                'Examify\Exams\Components\CheckPracticeSession' => 'ExamifyCheckPracticeSession',
                'Examify\Exams\Components\Charts' => 'ExamifyCharts',
                'Examify\Exams\Components\Administrators' => 'ExamifyAdministrators',
                'Examify\Exams\Components\AdminSchools' => 'ExamifyAdminSchools',
                'Examify\Exams\Components\AdminTeachers' => 'ExamifyAdminTeachers',
                'Examify\Exams\Components\AdminClasses' => 'ExamifyAdminClasses',
                'Examify\Exams\Components\AdminStudents' => 'ExamifyAdminStudents',
    	        'Examify\Exams\Components\AdminClass' => 'ExamifyAdminClass',
                'Examify\Exams\Components\AdminLicences' => 'ExamifyAdminLicences',
                'Examify\Exams\Components\Statistics' => 'ExamifyStatistics',
                'Examify\Exams\Components\AdminHomework' => 'ExamifyAdminHomework',
                'Examify\Exams\Components\AdminHomeworkItem' => 'ExamifyAdminHomeworkItem',
                'Examify\Exams\Components\Order' => 'ExamifyOrder',
                'Examify\Exams\Components\AdminVouchers' => 'ExamifyAdminVouchers',
                'Examify\Exams\Components\TimeLimit' => 'ExamifyTimeLimit',
                'Examify\Exams\Components\QuestionTypesTrials' => 'ExamifyQuestionTypesTrials'
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'examify.exams::mail.account_generated',
            'examify.exams::mail.account_generated_teacher',
            'examify.exams::mail.school_student_coupled',
            'examify.exams::mail.general_text'
        ];
    }

    public function registerPDFTemplates()
    {
        return [
            'examify.exams::pdf.invoice'
        ];
    }

    public function registerPDFLayouts()
    {
        return [
            'examify.exams::pdf.layouts.default'
        ];
    }

    public function registerSettings()
    {

    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'seedShuffle' => [$this, 'seedShuffle']
            ]
        ];
    }

    public function seedShuffle($array, $seed)
    {
        return $array->shuffle($seed);
    }

    public function boot()
    {

        // required for the Shoppingcart package
        $this->app->singleton(DatabaseManager::class, function ($app) {
            return $app->make('db');
        });

    	Event::listen('rainlab.user.register', function($user, $data) {

    		// find the user and register the setting of the level
    		$mylevel = $data['level'];

    		// in case it is empty, return false
    		if(empty($mylevel)){
    			return false;
    		}

    		// get the user as Examify object
    		$examifyUser = Users::find($user->id);
            $examifyUser->name = $data['firstname'];
            $examifyUser->save();

    		// set the usersetting
    		$examifyUser->setUserSetting('level', $mylevel);
    	});

        Event::listen('backend.form.extendFieldsBefore', function (\Backend\Widgets\Form $form) {

            if (
                !$form->getController() instanceof \RainLab\Pages\Controllers\Index ||
                !$form->model instanceof \RainLab\Pages\Classes\Page ||
                $form->isNested
            ) {
                return;
            }

            if (isset($form->model->viewBag['layout']) && $form->model->viewBag['layout'] == 'uitleg-subsection') {

                $fields = Yaml::parseFile(themes_path('examify/meta/nested-repeaters.yaml'));

                $form->secondaryTabs['fields']['viewBag[sections]'] = [
                    'tab'      => 'Sections',
                    'type'     => 'repeater',
                    'form'     => ['fields' => $fields],
                    'cssClass' => 'secondary-tab ',
                ];

                // For make the field translatable with RainLab.Translate plugin
                $form->model->translatable[] = 'viewBag[sections]';
            }

        });
    }
}
