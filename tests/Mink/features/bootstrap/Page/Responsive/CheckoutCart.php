<?php
namespace Responsive;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
    Behat\Behat\Context\Step;

class CheckoutCart extends \Emotion\CheckoutCart
{
    public $cssLocator = array(
        'aggregationAmounts' => 'div#aggregation',
        'totalAmount' => 'div > div.totalamount',
        'cartAmount' => 'div > p.textright',
        'shippingCosts' => 'div > div:nth-of-type(1)',
        'sumWithoutVat' => 'div > div.tax',
        'taxValue' => 'div#aggregation > div:nth-of-type(%d)',
        'taxRate' => 'div#aggregation_left > div:nth-of-type(%d)'
    );
}
