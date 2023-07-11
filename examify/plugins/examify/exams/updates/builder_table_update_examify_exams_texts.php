<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsTexts extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_texts', function($table)
        {
            $table->integer('myorder');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_texts', function($table)
        {
            $table->dropColumn('myorder');
        });
    }
}
