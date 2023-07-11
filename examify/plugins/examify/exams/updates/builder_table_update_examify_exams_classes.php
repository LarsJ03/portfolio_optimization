<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsClasses extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_classes', function($table)
        {
            $table->integer('schoolyear_id')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_classes', function($table)
        {
            $table->dropColumn('schoolyear_id');
        });
    }
}
