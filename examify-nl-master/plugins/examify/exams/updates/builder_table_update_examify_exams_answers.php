<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsAnswers extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_answers', function($table)
        {
            $table->integer('order');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_answers', function($table)
        {
            $table->dropColumn('order');
        });
    }
}
