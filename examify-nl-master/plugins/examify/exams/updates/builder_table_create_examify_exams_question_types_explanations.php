<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsQuestionTypesExplanations extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_question_types_explanations', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('question_type_id');
            $table->integer('course_id');
            $table->text('herkennen')->nullable();
            $table->text('aanpak')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('sort_order');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_question_types_explanations');
    }
}
