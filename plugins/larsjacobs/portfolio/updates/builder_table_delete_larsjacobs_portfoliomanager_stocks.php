<?php namespace LarsJacobs\Portfolio\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteLarsjacobsPortfoliomanagerStocks extends Migration
{
    public function up()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_stocks');
    }
    
    public function down()
    {
        Schema::create('larsjacobs_portfoliomanager_stocks', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('stock_name', 255);
            $table->string('stock_symbol', 255);
            $table->decimal('current_price', 10, 0);
        });
    }
}
