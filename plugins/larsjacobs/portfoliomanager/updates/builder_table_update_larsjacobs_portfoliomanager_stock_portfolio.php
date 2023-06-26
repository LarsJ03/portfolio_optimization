<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerStockPortfolio extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_stock_portfolio', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_stock_portfolio', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}
