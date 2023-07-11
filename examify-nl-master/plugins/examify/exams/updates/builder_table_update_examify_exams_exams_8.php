<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsExams8 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->integer('practice_mode_available')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->dropColumn('practice_mode_available');
        });
    }
}
