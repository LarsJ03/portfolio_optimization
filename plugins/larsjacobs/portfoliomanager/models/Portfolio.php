<?php namespace LarsJacobs\PortfolioManager\Models;

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
    public $table = 'larsjacobs_portfoliomanager_stock_portfolio';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

    public $belongsToMany = [
        'users' => [
            'RainLab\User\Models\User',
            'table' => 'larsjacobs_portfoliomanager_portfolio_users', // pivot table name
            'key' => 'portfolio_id', // key on the portfolio table pointing to the portfolio
            'otherKey' => 'user_id'
        ],
        'stocks' => [
            'LarsJacobs\PortfolioManager\Models\Stock',
            'table' => 'larsjacobs_portfoliomanager_stock_portfolio', // pivot table name
            'key' => 'portfolio_id', // key on the stock_portfolio table pointing to the portfolio
            'otherKey' => 'stock_id', // key on the pivot table pointing to the stock
            'pivot' => ['quantity'] // quantity is also included in the pivot table
        ]
    ];

    
}
