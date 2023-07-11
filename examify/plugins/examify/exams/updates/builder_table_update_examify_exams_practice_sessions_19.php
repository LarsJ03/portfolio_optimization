<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Examify\Exams\Models\PracticeSessions as PS;

class BuilderTableUpdateExamifyExamsPracticeSessions19 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->integer('partial_exam_id')->nullable()->default(0);
        });

        // update all the current practice sessions
        $pss = PS::all();

        foreach($pss as $ps)
        {
            if($ps->exam_id){
                if(!$ps->isFullExam())
                {
                    $ps->partial_exam_id = $ps->exam_id;
                    $ps->exam_id = 0;
                    $ps->save();
                }
            }
        }
    }
    
    public function down()
    {
        Schema::table('examify_exams_practice_sessions', function($table)
        {
            $table->dropColumn('partial_exam_id');
        });
    }
}
