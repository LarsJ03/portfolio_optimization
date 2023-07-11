<?php namespace LarsJacobs\Profile\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddNewFields extends Migration
{

    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->text('q1');
            $table->text('q2');
            
        });
    }

    public function down()
    {
        $table->dropDown([
            'q1',
            'q2'
        ]);
    }

}
