<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsPracticeSessions17 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->integer('question_types_trials_id')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->dropColumn('question_types_trials_id');
        });
    }
}
