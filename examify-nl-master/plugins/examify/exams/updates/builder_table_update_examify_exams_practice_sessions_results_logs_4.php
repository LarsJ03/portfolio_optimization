<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsPracticeSessionsResultsLogs4 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_practice_sessions_results_logs', function($table)
        {
            $table->renameColumn('completed', 'points_unanswered');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_practice_sessions_results_logs', function($table)
        {
            $table->renameColumn('points_unanswered', 'completed');
        });
    }
}
