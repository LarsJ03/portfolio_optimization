<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsClassesUsers extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_classes_users', function($table)
        {
            $table->integer('is_teacher')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_classes_users', function($table)
        {
            $table->dropColumn('is_teacher');
        });
    }
}
