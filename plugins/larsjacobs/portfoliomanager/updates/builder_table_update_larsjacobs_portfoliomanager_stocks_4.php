<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerStocks4 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_stocks', function($table)
        {
            $table->renameColumn('stock_id', 'id');
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_stocks', function($table)
        {
            $table->renameColumn('id', 'stock_id');
        });
    }
}
