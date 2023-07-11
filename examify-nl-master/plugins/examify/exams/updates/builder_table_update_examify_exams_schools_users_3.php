<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsSchoolsUsers3 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_schools_users', function($table)
        {
            $table->integer('is_school_admin')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_schools_users', function($table)
        {
            $table->dropColumn('is_school_admin');
        });
    }
}