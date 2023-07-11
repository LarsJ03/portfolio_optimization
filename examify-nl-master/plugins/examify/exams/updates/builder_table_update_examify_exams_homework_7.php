<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsHomework7 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_homework', function($table)
        {
            $table->text('child_ids')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_homework', function($table)
        {
            $table->dropColumn('child_ids');
        });
    }
}
