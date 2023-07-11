<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsSchoolsUsers extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_schools_users', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('school_id');
            $table->integer('user_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_schools_users');
    }
}
