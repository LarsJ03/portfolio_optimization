<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerStocksTimedata extends Migration
{
    public function up()
    {
        Schema::rename('larsjacobs_portfoliomanager_stocks', 'larsjacobs_portfoliomanager_stocks_timedata');
    }
    
    public function down()
    {
        Schema::rename('larsjacobs_portfoliomanager_stocks_timedata', 'larsjacobs_portfoliomanager_stocks');
    }
}
