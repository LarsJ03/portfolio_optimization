<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsOrders6 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_orders', function($table)
        {
            $table->integer('activated')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_orders', function($table)
        {
            $table->dropColumn('activated');
        });
    }
}
