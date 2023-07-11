<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsTexts3 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_texts', function($table)
        {
            $table->integer('printscreen_width')->nullable();
            $table->integer('printscreen_height')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_texts', function($table)
        {
            $table->dropColumn('printscreen_width');
            $table->dropColumn('printscreen_height');
        });
    }
}
