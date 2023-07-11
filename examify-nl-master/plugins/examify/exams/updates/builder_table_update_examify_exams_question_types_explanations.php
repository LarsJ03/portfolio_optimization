<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestionTypesExplanations extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_question_types_explanations', function($table)
        {
            $table->integer('sort_order')->default(100000)->change();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_question_types_explanations', function($table)
        {
            $table->integer('sort_order')->default(null)->change();
        });
    }
}
