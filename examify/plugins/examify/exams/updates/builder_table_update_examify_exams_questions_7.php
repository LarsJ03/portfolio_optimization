<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestions7 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->string('name')->nullable()->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->smallInteger('name')->nullable()->unsigned(false)->default(null)->change();
        });
    }
}
