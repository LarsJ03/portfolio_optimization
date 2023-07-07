<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerStocksTimedata2 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_stocks_timedata', function($table)
        {
            $table->dropColumn('stock_name');
            $table->dropColumn('stock_symbol');
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_stocks_timedata', function($table)
        {
            $table->string('stock_name', 255);
            $table->string('stock_symbol', 255);
        });
    }
}
