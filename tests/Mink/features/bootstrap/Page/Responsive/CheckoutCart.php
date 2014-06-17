<?php
namespace Responsive;

use Behat\Behat\Context\Step;

class CheckoutCart extends \Emotion\CheckoutCart
{
    public $cssLocator = array(
        'aggregationAmounts' => 'div#aggregation',
        'total' => 'div > div.totalamount',
        'sum' => 'div > p.textright',
        'shipping' => 'div > div:nth-of-type(1)',
        'sumWithoutVat' => 'div > div.tax',
        'taxValue' => 'div#aggregation > div:nth-of-type(%d)',
        'taxRate' => 'div#aggregation_left > div:nth-of-type(%d)'
    );
}