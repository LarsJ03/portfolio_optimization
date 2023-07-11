<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsLicenses extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_licenses', function($table)
        {
            $table->integer('school_id');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_licenses', function($table)
        {
            $table->dropColumn('school_id');
        });
    }
}
