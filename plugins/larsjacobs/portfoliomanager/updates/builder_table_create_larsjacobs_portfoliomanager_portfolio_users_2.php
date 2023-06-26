<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLarsjacobsPortfoliomanagerPortfolioUsers2 extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_portfoliomanager_portfolio_users', function($table)
        {
            $table->integer('user_id');
            $table->integer('portfolio_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_portfolio_users');
    }
}
