<?php namespace LarsJacobs\Stocks\Models;

use Model;

/**
 * Model
 */
class Stock extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'larsjacobs_stocks_';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

    public $belongsTo = [
        'portfolio' => [
            'LarsJacobs\Portfolio\Models\Portfolio',
            'key' => 'portfolio_id'
        ]
    ];

}
