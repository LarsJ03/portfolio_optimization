<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestionsAnswersLogs extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_questions_answers_logs', function($table)
        {
            $table->text('open_answer')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_questions_answers_logs', function($table)
        {
            $table->text('open_answer')->nullable(false)->change();
        });
    }
}
