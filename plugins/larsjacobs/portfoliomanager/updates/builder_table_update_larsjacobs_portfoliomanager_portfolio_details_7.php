<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerPortfolioDetails7 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->increments('portfolio_id')->change();
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->integer('portfolio_id')->change();
        });
    }
}
