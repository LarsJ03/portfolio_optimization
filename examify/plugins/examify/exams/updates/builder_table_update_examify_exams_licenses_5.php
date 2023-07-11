<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsLicenses5 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_licenses', function($table)
        {
            $table->integer('order_id')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_licenses', function($table)
        {
            $table->dropColumn('order_id');
        });
    }
}
