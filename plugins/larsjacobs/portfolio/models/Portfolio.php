<?php namespace LarsJacobs\Portfolio\Models;

use Model;

/**
 * Model
 */
class Portfolio extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'larsjacobs_portfolio_';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

    public $hasMany = [
        'stocks' => [
            'LarsJacobs\Stocks\Models\Stock',
            'key' => 'portfolio_id'
        ]
    ];

}
