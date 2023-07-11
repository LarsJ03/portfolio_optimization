<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsOrders5 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_orders', function($table)
        {
            $table->integer('voucher_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_orders', function($table)
        {
            $table->dropColumn('voucher_id');
        });
    }
}
