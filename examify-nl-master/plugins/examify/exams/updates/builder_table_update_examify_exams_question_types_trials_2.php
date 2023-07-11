<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestionTypesTrials2 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_question_types_trials', function($table)
        {
            $table->integer('course_id');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_question_types_trials', function($table)
        {
            $table->dropColumn('course_id');
        });
    }
}
