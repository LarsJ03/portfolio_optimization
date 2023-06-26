<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLarsjacobsPortfoliomanagerStocks extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_portfoliomanager_stocks', function($table)
        {
            $table->integer('stock_id');
            $table->string('stock_name');
            $table->string('stock_symbol');
            $table->decimal('stock_price', 10, 0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_stocks');
    }
}
