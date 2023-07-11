<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsPracticeSessionsResultsLogs6 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_practice_sessions_results_logs', function($table)
        {
            $table->integer('points_unanswered')->nullable(false)->change();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_practice_sessions_results_logs', function($table)
        {
            $table->integer('points_unanswered')->nullable()->change();
        });
    }
}
