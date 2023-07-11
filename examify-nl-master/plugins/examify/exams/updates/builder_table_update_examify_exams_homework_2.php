<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsHomework2 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_homework', function($table)
        {
            $table->integer('ntexts')->nullable()->default(0);
            $table->integer('nquestions')->nullable()->default(0);
            $table->text('texts')->nullable();
            $table->text('questions')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_homework', function($table)
        {
            $table->dropColumn('ntexts');
            $table->dropColumn('nquestions');
            $table->dropColumn('texts');
            $table->dropColumn('questions');
        });
    }
}
