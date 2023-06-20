<?php namespace LarsJacobs\Portfolio;

use System\Classes\PluginBase;
use RainLab\User\Models\User;
use LarsJacobs\Portfolio\Models\Portfolio; 

/**
 * Plugin class
 */
class Plugin extends PluginBase
{

    
    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
{
    User::extend(function($model) {
        $model->hasMany['portfolios'] = ['LarsJacobs\Portfolio\Models\Portfolio'];
    });

    // Corrected the Portfolio model namespace
    \LarsJacobs\Portfolio\Models\Portfolio::extend(function($model) {
        $model->hasMany['stocks'] = ['LarsJacobs\Stocks\Models\Stock'];
    });
}



    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }
}
