<?php namespace Examify\Exams\Models;

use Model;
use \Examify\Exams\Models\Questions as Questions;

use October\Rain\Exception\ValidationException as ValidationException;


/**
 * Model
 */
class QuestionTypesTrials extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sortable;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_question_types_trials';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $jsonable = [
        'question_ids_content',
        'question_ids'
    ];

    // belongs to many courses
    public $belongsTo = [
        'course' => ['Examify\Exams\Models\Courses'],
        'question_type' => ['Examify\Exams\Models\QuestionTypes']
    ];

    public function course()
    {
        return $this->belongsTo('Examify\Exams\Models\Courses', 'course_id');
    }

    public function questionType()
    {
        return $this->belongsTo('Examify\Exams\Models\QuestionTypes', 'quesiton_type_id');
    }

    public function getTextOptions()
    {
        // get all the questions from the table
        $t = Texts::all();
        $display = $t->map(function($item){
            return '[' . $item->exam->year . '|' . $item->exam->tijdvak . '] Tekst ' . $item->myorder . ': ' . $item->name;
        });

        return array_combine($t->pluck('id')->values()->all(), $display->all());
    }

    public function getFullName()
    {
        return $this->name . ' (' . $this->course->name . ' ' . $this->course->level . ')';
    }

    public function getQuestionOptions($value)
    {

        if($value){ 
            $q = Questions::find($value);
            $q = Questions::where('text_id', $q->text_id)->orderBy('question_nr')->get();
        }
        else {
            $q = Questions::where('text_id', $this->text)->orderBy('question_nr')->get();
        }
        $display = $q->map(function($item){
            return 'Q' . $item->question_nr . ': ' . $item->name;
        });

        return array_combine($q->pluck('id')->values()->all(), $display->all());
    }

    public function beforeSave()
    {
        $qs = $this->question_ids_content;

        if(empty($qs)){
            throw new ValidationException(['empty_question_list' => 'Er moet minstens 1 vraag gekoppeld zijn.']);
        }

        $myresults = [];

        foreach($qs as $q)
        {
            $myresults[] = $q['question'];
        }

        $this->question_ids = $myresults;
    }
}
