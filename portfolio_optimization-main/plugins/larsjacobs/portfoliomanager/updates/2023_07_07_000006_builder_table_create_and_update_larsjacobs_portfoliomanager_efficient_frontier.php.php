<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAndUpdateLarsjacobsPortfoliomanagerEfficientFrontier extends Migration
{
    public function up()
    {
        Schema::create('larsjacobs_portfoliomanager_efficient_frontier', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('portfolio_id');
            $table->decimal('frontier_risks', 10, 0);
            $table->decimal('frontier_returns', 10, 0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('larsjacobs_portfoliomanager_efficient_frontier');
    }
}
