<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsAnswers5 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_answers', function($table)
        {
            $table->string('name', 511)->change();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_answers', function($table)
        {
            $table->string('name', 191)->change();
        });
    }
}
