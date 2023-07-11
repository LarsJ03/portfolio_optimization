<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsOrders2 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_orders', function($table)
        {
            $table->string('mollie_id', 127)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_orders', function($table)
        {
            $table->dropColumn('mollie_id');
        });
    }
}
