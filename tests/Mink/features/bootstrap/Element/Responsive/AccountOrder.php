<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: AccountOrder
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountOrder extends \Shopware\Tests\Mink\Element\Emotion\AccountOrder
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => '.order--item'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
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
        ];
    }
}
