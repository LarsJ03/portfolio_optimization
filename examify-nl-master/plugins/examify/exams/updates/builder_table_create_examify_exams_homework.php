<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsHomework extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_homework', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('school_id')->nullable();
            $table->integer('class_id')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_homework');
    }
}
