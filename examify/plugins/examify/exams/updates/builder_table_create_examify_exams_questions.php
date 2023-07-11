<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsQuestions extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_questions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('exam_id');
            $table->integer('question_nr');
            $table->text('intro')->nullable();
            $table->text('main')->nullable();
            $table->text('outro')->nullable();
            $table->integer('points');
            $table->integer('type');
            $table->text('answers')->nullable();
            $table->text('explanation');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->primary(['exam_id','question_nr']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_questions');
    }
}
