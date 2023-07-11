<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsPracticeSessions11 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->integer('time_limit_mins')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->dropColumn('time_limit_mins');
        });
    }
}
