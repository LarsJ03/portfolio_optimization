<?php namespace LarsJacobs\PortfolioManager\Models;

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
    public $table = 'larsjacobs_portfoliomanager_stocks';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

    public $belongsToMany = [
        'stockportfolios' => [
            'LarsJacobs\PortfolioManager\Models\Portfolio', // Correct model is Portfolio
            'table' => 'larsjacobs_portfoliomanager_stock_portfolio', // pivot table name
            'key' => 'stock_id', // Correct key on the stock table pointing to the stock
            'otherKey' => 'portfolio_id', // Correct key on the pivot table pointing to the portfolio
            'pivot' => ['quantity'] // quantity is also included in the pivot table
        ]
    ];
    

}
