<?php namespace LarsJacobs\Stocks\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsStocks3 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_stocks_', function($table)
        {
            $table->integer('portfolio_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_stocks_', function($table)
        {
            $table->dropColumn('portfolio_id');
        });
    }
}
