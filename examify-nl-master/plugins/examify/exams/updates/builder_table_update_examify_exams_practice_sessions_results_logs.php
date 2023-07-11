<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsPracticeSessionsResultsLogs extends Migration
{
    public function up()
    {
        Schema::rename('examify_exams_practice_sessions_results_log', 'examify_exams_practice_sessions_results_logs');
    }
    
    public function down()
    {
        Schema::rename('examify_exams_practice_sessions_results_logs', 'examify_exams_practice_sessions_results_log');
    }
}
