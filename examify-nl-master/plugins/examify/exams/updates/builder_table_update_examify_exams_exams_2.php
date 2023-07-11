<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsExams2 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->text('description')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_exams', function($table)
        {
            $table->text('description')->nullable(false)->change();
        });
    }
}
