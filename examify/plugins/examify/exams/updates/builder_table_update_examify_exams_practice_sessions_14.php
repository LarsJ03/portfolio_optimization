<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsPracticeSessions14 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->smallInteger('cached_answers')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->dropColumn('cached_answers');
        });
    }
}
