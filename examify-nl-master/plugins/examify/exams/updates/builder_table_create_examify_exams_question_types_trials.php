<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsQuestionTypesTrials extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_question_types_trials', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 1023)->nullable();
            $table->string('question_ids', 4095)->nullable();
            $table->integer('question_type_id');
            $table->integer('visible')->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_question_types_trials');
    }
}
