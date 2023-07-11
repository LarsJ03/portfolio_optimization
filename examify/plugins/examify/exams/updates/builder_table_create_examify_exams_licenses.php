<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateExamifyExamsLicenses extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_licenses', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('class_id');
            $table->integer('course_id');
            $table->string('key');
            $table->integer('user_id')->nullable();
            $table->integer('activated')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('examify_exams_licenses');
    }
}
