<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsAnswers4 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_answers', function($table)
        {
            $table->renameColumn('order', 'myorder');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_answers', function($table)
        {
            $table->renameColumn('myorder', 'order');
        });
    }
}
