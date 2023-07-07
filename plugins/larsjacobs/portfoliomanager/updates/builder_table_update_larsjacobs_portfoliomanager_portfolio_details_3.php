<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerPortfolioDetails3 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->string('name', 255)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->string('name', 255)->nullable(false)->change();
        });
    }
}
