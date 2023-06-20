<?php namespace LarsJacobs\Stocks\Components;

use Cms\Classes\ComponentBase;
use LarsJacobs\Stocks\Models\Portfolio;

class PortfolioComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Portfolio Component',
            'description' => 'Displays a portfolio'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['portfolio'] = $this->loadPortfolio();
    }

    protected function loadPortfolio()
    {
        return Portfolio::all();
    }

    
}