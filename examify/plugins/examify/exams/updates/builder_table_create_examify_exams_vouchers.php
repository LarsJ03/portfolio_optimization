<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsVouchers extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_vouchers', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('code', 127);
            $table->integer('limit')->default(999999);
            $table->integer('count')->default(0);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->double('discount_eur', 10, 0)->nullable();
            $table->double('discount_perc', 10, 0)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_vouchers');
    }
}
