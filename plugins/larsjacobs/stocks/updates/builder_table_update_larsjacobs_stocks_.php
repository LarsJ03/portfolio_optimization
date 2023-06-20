<?php namespace LarsJacobs\Stocks\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsStocks extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_stocks_', function($table)
        {
            $table->dropPrimary(['stock_symbol']);
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_stocks_', function($table)
        {
            $table->primary(['stock_symbol']);
        });
    }
}
