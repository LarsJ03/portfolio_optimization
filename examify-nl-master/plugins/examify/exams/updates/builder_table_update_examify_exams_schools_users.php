<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsSchoolsUsers extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_schools_users', function($table)
        {
           // $table->primary(['school_id','user_id']);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_schools_users', function($table)
        {
           // $table->dropPrimary(['school_id','user_id']);
        });
    }
}
