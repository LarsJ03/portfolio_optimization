<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestionTypesTrials4 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_question_types_trials', function($table)
        {
            $table->integer('myorder')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_question_types_trials', function($table)
        {
            $table->dropColumn('myorder');
        });
    }
}
