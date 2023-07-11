<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsOrders extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_orders', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 127)->nullable();
            $table->string('status', 127)->nullable();
            $table->string('invoice', 2047)->nullable();
            $table->integer('user_id');
            $table->text('courses');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_orders');
    }
}
