<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsExams4 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->text('texts')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->dropColumn('texts');
        });
    }
}
