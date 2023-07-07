<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerStocks3 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_stocks', function($table)
        {
            $table->text('stock_name');
            $table->text('stock_symbol');
            $table->dropColumn('name');
            $table->dropColumn('symbol');
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_stocks', function($table)
        {
            $table->dropColumn('stock_name');
            $table->dropColumn('stock_symbol');
            $table->text('name');
            $table->text('symbol');
        });
    }
}
