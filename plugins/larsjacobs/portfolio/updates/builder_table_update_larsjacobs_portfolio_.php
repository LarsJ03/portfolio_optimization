<?php namespace LarsJacobs\Portfolio\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfolio extends Migration
{
    public function up()
    {
        Schema::rename('larsjacobs_portfoliomanager_portfolio', 'larsjacobs_portfolio_');
        Schema::table('larsjacobs_portfolio_', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::rename('larsjacobs_portfolio_', 'larsjacobs_portfoliomanager_portfolio');
        Schema::table('larsjacobs_portfoliomanager_portfolio', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}
