<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestionTypesTrials5 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_question_types_trials', function($table)
        {
            $table->renameColumn('myorder', 'sort_order');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_question_types_trials', function($table)
        {
            $table->renameColumn('sort_order', 'myorder');
        });
    }
}
