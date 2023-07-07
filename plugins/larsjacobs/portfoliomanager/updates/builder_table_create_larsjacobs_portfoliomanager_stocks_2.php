<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLarsjacobsPortfoliomanagerStocks2 extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_portfoliomanager_stocks', function($table)
        {
            $table->increments('stock_id')->unsigned();
            $table->text('name');
            $table->text('symbol');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_stocks');
    }
}
