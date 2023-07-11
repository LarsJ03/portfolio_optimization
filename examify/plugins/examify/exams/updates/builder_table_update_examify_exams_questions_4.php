<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateExamifyExamsQuestions4 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->dropPrimary(['exam_id','question_nr']);
        });
    }
    
    public function down()
    {
        Schema::table('examify_exams_questions', function($table)
        {
            $table->primary(['exam_id','question_nr']);
        });
    }
}
