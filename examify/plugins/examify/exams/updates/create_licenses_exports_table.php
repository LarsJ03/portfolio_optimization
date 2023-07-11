<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateLicensesExportsTable extends Migration
{
    public function up()
    {
        Schema::create('examify_exams_licenses_exports', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('examify_exams_licenses_exports');
    }
}
