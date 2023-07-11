<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestions extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->renameColumn('type', 'type_id');
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->renameColumn('type_id', 'type');
        });
    }
}
