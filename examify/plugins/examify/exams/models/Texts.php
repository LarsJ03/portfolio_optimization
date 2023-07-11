<?php namespace Examify\Exams\Models;

use Model;
use October\Rain\Exception\ValidationException as ValidationException;

/**
 * Model
 */
class Texts extends Model
{
    
    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_texts';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    // relationships
    public $belongsTo = [
        'exam' => 'Examify\Exams\Models\Exams'
    ];

    public function questions()
    {
        return $this->hasMany('Examify\Exams\Models\Questions', 'text_id')->orderBy('question_nr');
    }

    public $hasMany = [
        'questions' => ['Examify\Exams\Models\Questions', 'key' => 'text_id']
    ];

    public function getTextDetailsAttribute()
    {
        return $this->exam->course->level . ' / ' . $this->exam->course->name . ' / ' . $this->exam->year . ' / tijdvak ' . $this->exam->tijdvak . ' / Tekst ' . $this->myorder . ' / ' . $this->name;
    }
}
