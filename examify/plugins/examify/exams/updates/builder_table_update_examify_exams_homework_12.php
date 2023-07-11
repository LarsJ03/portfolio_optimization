<?php namespace Examify\Exams\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Examify\Exams\Models\Homework as Homework;

class BuilderTableUpdateExamifyExamsHomework12 extends Migration
{
    public function up()
    {
        Schema::table('examify_exams_homework', function($table)
        {
            $table->integer('exam_id')->nullable()->default(0);
        });

        // loop over the homework sessions and determine if an exam_id needs to be coupled
        $hws = Homework::all();
        foreach($hws as $hw)
        {
            if($hw->child_ids){
                $children = $hw->getChildren();

                if(!$children->count()){
                    continue;
                }

                $c = $children->first();
                $ps = $c->practiceSessions()->first();

                if($ps){
                    $hw->exam_id = $ps->partial_exam_id;
                    $hw->save();
                }
                continue;
            }

            $ps = $hw->practiceSessions()->first();    

            if($ps && $ps->exam_id){
                $hw->exam_id = $ps->exam_id;
                $hw->save();
            }
        }
    }
    
    public function down()
    {
        Schema::table('examify_exams_homework', function($table)
        {
            $table->dropColumn('exam_id');
        });
    }
}
