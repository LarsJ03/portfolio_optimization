<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateLarsjacobsPortfoliomanagerPortfolioDetails extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->integer('portfolio_id');
            $table->text('description');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_portfolio_details');
    }
}
