<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsClasses2 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_classes', function($table)
        {
            $table->renameColumn('schoolyear_id', 'schoolyear');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_classes', function($table)
        {
            $table->renameColumn('schoolyear', 'schoolyear_id');
        });
    }
}
