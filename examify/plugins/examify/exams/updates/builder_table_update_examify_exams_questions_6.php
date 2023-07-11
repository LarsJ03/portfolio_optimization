<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestions6 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->renameColumn('answers', 'answers_content');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->renameColumn('answers_content', 'answers');
        });
    }
}
