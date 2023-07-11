<?php namespace Examify\Exams\Classes; 

class Pricing 
{
    public $value;

    public static function getDiscountForTwoYears()
    {
        return 30;
    }

    public static function getPrice($ncourses)
    {
        switch ($ncourses) {
            case 0:
                return new Pricing(0.00);
                break;

            case 1:
                return new Pricing(29.95);
                break;

            case 2:
                return new Pricing(49.95);
                break;

            case 3:
                return new Pricing(64.95);
                break;

            default:
                // the price is the price of 3 + 10 euro for each extra language
                $price = Pricing::getPrice(3);
                $price->add(10 * ($ncourses - 3));
                return $price;
                break;
        }
    }

    public function __construct($ini)
    {
        if($ini instanceof Pricing)
        {
            $this->value = $ini->value;
            $this->roundValue();
            return;
        }

        $this->value = $ini;
        $this->roundValue();
    }

    public function add($value)
    {
        if($value instanceof Pricing)
        {
            $this->value += $value->value;
            $this->roundValue();
            return;
        }

        $this->value += $value;
        $this->roundValue();
    }

    public function roundValue()
    {
        $this->value = round($this->value, 2);
    }

    public function subtract($value)
    {
        if($value instanceof Pricing)
        {
            $this->value -= $value->value;
            $this->roundValue();
            return;
        }

        $this->value -= $value;
        $this->roundValue();
    }

    public function multiplyBy($value)
    {
        if($value instanceof Pricing)
        { 
            $this->value *= $this->value; 
            $this->roundValue();
            return;
        }

        $this->value *= $value; 
        $this->roundValue();
    }

    public function applyDiscountPercentage($discount)
    {
        $this->value *= (100 - $discount) / 100;
        $this->roundValue();
    }

    public function getDiscountForPercentage($discount)
    {
        return new Pricing($this->value * $discount / 100);
    }

    public function getDutchFormat()
    {
        return number_format($this->value, 2, ',', '.');
    }

    public function getMollieFormat()
    {
        return number_format($this->value, 2, '.', '');
    }

    public function getBase()
    {
        return intval($this->value);
    }
    public function getDecimals()
    {
        return intval(round(($this->value - $this->getBase()) * 100, 0));
    }
}