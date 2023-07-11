<?php namespace Examify\Exams\Models;

use Model;
use Carbon\Carbon;
use \Examify\Exams\Classes\Pricing as Pricing;

/**
 * Model
 */
class Vouchers extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_vouchers';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public function isActive()
    {
        // check if the current date is within the start and end time
        $today = Carbon::now();

        if($today < $this->start_time)
        {
            return -1;
        }
        if($today > $this->end_time)
        {
            return -2;
        }

        if($this->count >= $this->limit)
        {
            return -3;
        }

        return 1;
    }

    public function getDiscountText()
    {
        if($this->discount_eur > 0)
        {
            $price = new Pricing($this->discount_eur);
            return $price->getDutchFormat() . ' euro korting';
        }
        else {
            return $this->discount_perc . '% korting';
        }
    }
}
