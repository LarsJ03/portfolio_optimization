<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerPortfolioDetails4 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->decimal('volatility', 10, 0);
            $table->decimal('expected_return', 10, 0);
            $table->text('description')->nullable()->change();
            $table->string('name', 255)->nullable(false)->change();
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_portfolio_details', function($table)
        {
            $table->dropColumn('volatility');
            $table->dropColumn('expected_return');
            $table->text('description')->nullable(false)->change();
            $table->string('name', 255)->nullable()->change();
        });
    }
}
