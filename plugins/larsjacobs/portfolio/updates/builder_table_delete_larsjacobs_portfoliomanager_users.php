<?php namespace LarsJacobs\Portfolio\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteLarsjacobsPortfoliomanagerUsers extends Migration
{
    public function up()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_users');
    }
    
    public function down()
    {
        Schema::create('larsjacobs_portfoliomanager_users', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('username', 255);
            $table->string('password', 255);
            $table->string('email', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
}
