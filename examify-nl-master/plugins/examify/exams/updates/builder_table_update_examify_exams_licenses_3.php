<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsLicenses3 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_licenses', function($table)
        {
            $table->integer('class_id')->default(0)->change();
            $table->integer('course_id')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_licenses', function($table)
        {
            $table->integer('class_id')->default(null)->change();
            $table->integer('course_id')->default(null)->change();
        });
    }
}
