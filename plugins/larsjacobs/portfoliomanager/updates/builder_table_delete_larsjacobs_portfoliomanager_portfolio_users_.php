<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteLarsjacobsPortfoliomanagerPortfolioUsers extends Migration
{
    public function up()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_portfolio_users_');
    }
    
    public function down()
    {
        Schema::create('larsjacobs_portfoliomanager_portfolio_users_', function($table)
        {
            $table->integer('user_id');
            $table->integer('portfolio_id');
        });
    }
}
