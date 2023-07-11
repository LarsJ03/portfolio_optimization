<?php namespace LarsJacobs\Portfoliomanager\Models;

use Model;

/**
 * Model
 */
class EfficientFrontier extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'larsjacobs_portfoliomanager_efficient_frontier';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

    public $belongsTo = [
        'portfolio' => ['LarsJacobs\Portfoliomanager\Models\Portfolio']
    ];

}
