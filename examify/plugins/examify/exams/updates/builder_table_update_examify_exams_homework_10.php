<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsHomework10 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_homework', function($table)
        {
            $table->integer('split_exam')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_homework', function($table)
        {
            $table->dropColumn('split_exam');
        });
    }
}
