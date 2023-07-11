<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsVouchers extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_vouchers', function($table)
        {
            $table->string('name', 511);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_vouchers', function($table)
        {
            $table->dropColumn('name');
        });
    }
}
