<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsQuestionsAnswersLogs extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_questions_answers_logs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('user_id');
            $table->integer('question_id');
            $table->integer('answer_id')->nullable();
            $table->integer('points')->nullable();
            $table->text('open_answer');
            $table->integer('practice_session_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('is_final')->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_questions_answers_logs');
    }
}
