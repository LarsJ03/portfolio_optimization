<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerStockPortfolio2 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_stock_portfolio', function($table)
        {
            $table->increments('id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_stock_portfolio', function($table)
        {
            $table->dropColumn('id');
        });
    }
}
