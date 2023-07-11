<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsPracticeSessions extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->renameColumn('oefenmodus', 'leermodus');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->renameColumn('leermodus', 'oefenmodus');
        });
    }
}
