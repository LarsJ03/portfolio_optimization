<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestionTypesTrials3 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_question_types_trials', function($table)
        {
            $table->text('question_ids_content')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_question_types_trials', function($table)
        {
            $table->dropColumn('question_ids_content');
        });
    }
}
