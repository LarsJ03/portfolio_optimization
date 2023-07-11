<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAndUpdateLarsjacobsPortfoliomanagerPortfolioDetails extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->increments('portfolio_id');
            $table->text('description');
            $table->string('name', 255)->nullable();
            $table->decimal('volatility', 10, 4);
            $table->decimal('expected_return', 10, 4);
            $table->string('investment_strategy');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_portfolio_details');
    }
}
