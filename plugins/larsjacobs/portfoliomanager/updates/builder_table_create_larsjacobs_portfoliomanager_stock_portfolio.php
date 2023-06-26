<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLarsjacobsPortfoliomanagerStockPortfolio extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_portfoliomanager_stock_portfolio', function($table)
        {
            $table->integer('portfolio_id');
            $table->integer('stock_id');
            $table->integer('quantity');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_stock_portfolio');
    }
}
