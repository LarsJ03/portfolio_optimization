<?php namespace LarsJacobs\Portfoliomanager\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsPortfoliomanagerStocks extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_portfoliomanager_stocks', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_portfoliomanager_stocks', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}
