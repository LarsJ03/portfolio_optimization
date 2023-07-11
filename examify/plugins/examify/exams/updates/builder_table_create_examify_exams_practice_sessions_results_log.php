<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsPracticeSessionsResultsLog extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_practice_sessions_results_log', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('practice_session_id');
            $table->integer('question_id');
            $table->integer('question_type_id');
            $table->integer('points_achieved');
            $table->integer('points_available');
            $table->integer('user_id');
            $table->integer('course_id');
            $table->integer('exam_id');
            $table->integer('text_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_practice_sessions_results_log');
    }
}
