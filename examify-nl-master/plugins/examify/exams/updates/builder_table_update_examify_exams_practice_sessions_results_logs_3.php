<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsPracticeSessionsResultsLogs3 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_practice_sessions_results_logs', function($table)
        {
            $table->integer('completed')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_practice_sessions_results_logs', function($table)
        {
            $table->dropColumn('completed');
        });
    }
}
