<?php namespace LarsJacobs\Portfoliomanager\Models;

use Model;

/**
 * Model
 */
class PortfolioDetail extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'larsjacobs_portfoliomanager_portfolio_details';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

}
