<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLarsjacobsPortfoliomanagerStocksTimedata extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_portfoliomanager_stocks_timedata', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('stock_id');
            $table->integer('price');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_stocks_timedata');
    }
}
