<?php namespace LarsJacobs\Stocks\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateLarsjacobsStocks2 extends Migration
{
    public function up()
    {
        Schema::table('larsjacobs_stocks_', function($table)
        {
            $table->increments('id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('larsjacobs_stocks_', function($table)
        {
            $table->dropColumn('id');
        });
    }
}
