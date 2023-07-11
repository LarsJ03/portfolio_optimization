<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsSchoolsUsers4 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_schools_users', function($table)
        {
            $table->integer('schoolyear')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_schools_users', function($table)
        {
            $table->dropColumn('schoolyear');
        });
    }
}
