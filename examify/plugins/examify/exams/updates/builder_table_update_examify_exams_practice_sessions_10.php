<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsPracticeSessions10 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->integer('deleted')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->dropColumn('deleted');
        });
    }
}