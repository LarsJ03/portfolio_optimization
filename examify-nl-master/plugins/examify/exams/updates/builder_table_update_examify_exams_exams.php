<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsExams extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->integer('course_id');
            $table->integer('year');
            $table->integer('tijdvak')->default(1);
            $table->dropColumn('name');
            $table->dropColumn('level');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->dropColumn('course_id');
            $table->dropColumn('year');
            $table->dropColumn('tijdvak');
            $table->string('name', 191);
            $table->string('level', 191);
        });
    }
}
