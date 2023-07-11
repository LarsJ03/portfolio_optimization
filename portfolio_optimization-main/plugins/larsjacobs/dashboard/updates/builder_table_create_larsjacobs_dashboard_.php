<?php namespace LarsJacobs\Dashboard\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLarsjacobsDashboard extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_dashboard_', function($table)
        {
            $table->increments('id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('larsjacobs_dashboard_');
    }
}
