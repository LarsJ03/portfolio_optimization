<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsSchoolyears extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_schoolyears', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('year', 31);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_schoolyears');
    }
}
