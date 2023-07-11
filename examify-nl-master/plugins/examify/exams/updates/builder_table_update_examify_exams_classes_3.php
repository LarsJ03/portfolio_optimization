<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsClasses3 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_classes', function($table)
        {
            $table->integer('schoolyear')->default(2019)->change();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_classes', function($table)
        {
            $table->integer('schoolyear')->default(0)->change();
        });
    }
}
