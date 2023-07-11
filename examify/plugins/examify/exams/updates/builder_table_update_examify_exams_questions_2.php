<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestions2 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->smallInteger('name')->nullable();
            $table->dropColumn('main');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->dropColumn('name');
            $table->text('main')->nullable();
        });
    }
}
