<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsPracticeSessions extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_practice_sessions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->text('settings')->nullable();
            $table->integer('oefenmodus')->nullable()->default(0);
            $table->integer('finished')->nullable()->default(0);
            $table->integer('totalpointsavailable')->nullable();
            $table->integer('totalpointsachieved')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_practice_sessions');
    }
}
