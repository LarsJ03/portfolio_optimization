<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsAnswers3 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_answers', function($table)
        {
            $table->integer('points')->nullable(false)->change();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_answers', function($table)
        {
            $table->integer('points')->nullable()->change();
        });
    }
}
