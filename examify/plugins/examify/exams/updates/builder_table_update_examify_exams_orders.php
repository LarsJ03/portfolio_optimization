<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsOrders extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_orders', function($table)
        {
            $table->text('pricing');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_orders', function($table)
        {
            $table->dropColumn('pricing');
        });
    }
}
