<?php namespace Larsjacobs\Profile;

use System\Classes\PluginBase;
use Rainlab\User\Controllers\Users as UsersController;
use Rainlab\User\Models\User as UsersModel;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function boot() {

        UsersModel::extend(function($model){
            $model->addFillable([
                'q1',
                'q2'
            ]);
        });

        UsersController::extendFormFields(function($form, $model, $context) {

            $form->addTabFields([
                'q1' => [
                    'label' => 'Question 1',
                    'type' => 'textarea',
                    'tab' => 'Profile'
                ],

                'q2' => [
                    'label' => 'Question 2',
                    'type' => 'textarea',
                    'tab' => 'Profile'
                ]
            ]);
        });
    }
}
