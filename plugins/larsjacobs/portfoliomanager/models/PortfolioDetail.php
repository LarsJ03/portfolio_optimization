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

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['name', 'description', 'created_at', 'updated_at'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'portfolio' => [
            'LarsJacobs\Portfoliomanager\Models\Portfolio',
            'key' => 'portfolio_id'
        ]
    ];
}

