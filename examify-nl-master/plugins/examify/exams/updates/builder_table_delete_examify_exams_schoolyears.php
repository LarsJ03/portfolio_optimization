<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteExamifyExamsSchoolyears extends Migration
{
    public function up()
    {
        Schema::dropIfExists('examify_exams_schoolyears');
    }
    
    public function down()
    {
        Schema::create('examify_exams_schoolyears', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('year', 31);
        });
    }
}
