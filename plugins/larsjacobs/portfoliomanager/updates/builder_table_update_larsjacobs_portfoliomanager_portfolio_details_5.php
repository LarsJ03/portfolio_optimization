<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerPortfolioDetails5 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->string('investment_strategy');
            $table->decimal('volatility', 10, 4)->nullable(false)->change();
            $table->decimal('expected_return', 10, 4)->nullable(false)->change();
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->dropColumn('investment_strategy');
            $table->decimal('volatility', 10, 4)->nullable()->change();
            $table->decimal('expected_return', 10, 4)->nullable()->change();
        });
    }
}
