<?php

namespace Element\Responsive;

class AccountOrder extends \Element\Emotion\AccountOrder
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => '.order--item');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'date' => '.order--date > .column--value',
            'number' => '.order--number > .column--value',
            'footerDate' => 'div + .order--details .column--info-data > p:nth-of-type(1)',
            'footerNumber' => 'div + .order--details .column--info-data > p:nth-of-type(2)',
            'positions' => 'div + .order--details > .orders--table-header ~ .panel--tr:not(.is--odd)',
            'product' => '.order--name',
            'currentPrice' => '.order--current-price',
            'quantity' => '.order--quantity > .column--value',
            'price' => '.order--price > .column--value',
            'sum' => '.order--amount > .column--value'
        );
    }
}
