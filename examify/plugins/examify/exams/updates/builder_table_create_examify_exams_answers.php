<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsAnswers extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_answers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->integer('points')->default(0);
            $table->integer('attempts')->default(0);
            $table->integer('correct')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('question_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_answers');
    }
}
