<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsExams7 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->integer('visible')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->dropColumn('visible');
        });
    }
}