<?php namespace Examify\Exams\Models;

use Model;
use Examify\Exams\Models\Texts as Texts;
use Auth;
use Examify\Exams\Models\Users as Users;

/**
 * Model
 */
class Exams extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_exams';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $jsonable = ['texts_content'];

    // belongs to many courses
    public $belongsTo = [
        'course' => ['Examify\Exams\Models\Courses'],
    ];

    public function course()
    {
        return $this->belongsTo('Examify\Exams\Models\Courses', 'course_id');
    }

    public function texts()
    {
        return $this->hasMany('Examify\Exams\Models\Texts', 'exam_id')->orderBy('myorder');
    }

    public function getNumberOfQuestions()
    {
        $texts = $this->getTexts();
        $q = collect([]);
        foreach($texts as $text)
        {
            $q->push($text->questions()->count());
        }
        return $q->sum();
    }

    public function getQuestions()
    {
        $texts = $this->getTexts();
        $q = collect([]);
        foreach($texts as $text)
        {
            $q->push($text->questions()->get());
        }
        return $q->flatten();
    }

    public function getTexts()
    {
        return $this->texts()->get();
    }

    public $hasMany = [
        'texts' => ['Examify\Exams\Models\Texts', 'key' => 'exam_id']
    ];

    public function getExamDetailsAttribute()
    {
        return $this->course->level . ' / ' . $this->course->name . ' / ' . $this->year . ' / tijdvak ' . $this->tijdvak;
    }

    public function scopeVisible($query)
    {
        return $query->where('visible', 1);
    }

    public function scopeForFree($query)
    {
        return $query->where('for_free', 1);
    }

    public function scopeAvailableForUser($query)
    {
        $user = Users::getUser();
        
        $mycourses = $user->getSubscribedCourses();

        if(!$user || !($mycourses->count())){
            return $query->where('for_free', 1);
        }

        $myexams = $mycourses->pluck('exams')->flatten();
        $myexams_ids = $myexams->pluck('id');

        $query->whereIn('id', $myexams_ids)->orWhere('for_free', 1);

        
    }

    public function scopePracticeMode($query, $PracticeModeAvailable)
    {
        // return the standard query if the practice mode available is false, since it does not matter if it has the practice mode available if the user does not ask for practice mode
        if($PracticeModeAvailable){
            return $query->where('practice_mode_available', $PracticeModeAvailable);
        }

        return $query;
    }

    // after save, detach the texts and put them in a separate table
    public function afterSave() {

        // if there are no updates on the texts_content, just return since there are no checks to be performed (speeds up)
        if(!($this->isDirty(['texts_content']))){
            //return;
        }

        // There are changes when the server reached this point. This means that we should check whether texts should be added to the Texts table, or texts should be removed

        // the elements to add is equal to the number of texts minus the number of texts already stored in the database
        $nroftextsstored = $this->texts()->count();

        $count = 0;

        // loop over the texts and add / modify them in the database
        foreach($this->texts_content as $mytext){

            $count = $count + 1;

            // in case the index is larger than the number of texts already stored, create a new entry and save it
            // + 1 to compensate that index of first element is 0
            if($count > $nroftextsstored){
                $updateText = new Texts;
                $updateText->exam_id = $this->id;
                $updateText->myorder = $count;
            }
            else {
                $updateText = $this->texts()->where('myorder', $count)->first();
            }

            if(!array_key_exists('printscreen', $mytext)){
                $mytext['printscreen'] = '';
            }

            // store the name
            $updateText->name = $mytext['name'];
            $updateText->printscreen = $mytext['printscreen'];

            // in case printscreen is not empty, save the image size
            if(!empty($mytext['printscreen']))
            {
                $imagesize = getimagesize(\System\Classes\MediaLibrary::url($mytext['printscreen']));
                //$updateText->printscreen_width = $imagesize;
                //$updateText->printscreen_height = $imagesize[11234];

                // set the max_width manually
                $updateText->printscreen_width = $mytext['max_width'];
            }
            else {
                $updateText->printscreen_width = 0;
                $updateText->printscreen_height = 0;
            }
            $updateText->save();
            
        }

        // delete all old texts
        $this->texts()->where('myorder', '>', $count)->delete();
    }
}
