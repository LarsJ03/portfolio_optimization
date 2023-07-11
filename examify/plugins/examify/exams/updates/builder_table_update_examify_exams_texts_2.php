<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsTexts2 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_texts', function($table)
        {
            $table->string('printscreen', 2047)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_texts', function($table)
        {
            $table->dropColumn('printscreen');
        });
    }
}
