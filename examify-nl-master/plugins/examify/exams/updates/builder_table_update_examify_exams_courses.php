<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsCourses extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_courses', function($table)
        {
            $table->integer('for_sale')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_courses', function($table)
        {
            $table->dropColumn('for_sale');
        });
    }
}
