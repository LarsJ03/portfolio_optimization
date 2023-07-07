<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLarsjacobsPortfoliomanagerStockPortfolio2 extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_portfoliomanager_stock_portfolio', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('portfolio_id');
            $table->integer('stock_id');
            $table->integer('quantity');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_stock_portfolio');
    }
}
