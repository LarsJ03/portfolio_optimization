<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsClassesUsers extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_classes_users', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('class_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_classes_users');
    }
}
