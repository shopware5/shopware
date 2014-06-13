<?php

namespace Responsive;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;

class HeaderCart extends \Emotion\HeaderCart
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'li.navigation--entry.entry--cart');

    public $cssLocator = array(
        'quantity' => 'span.cart--quantity',
        'amount' => 'span.cart--amount',
        'link' => 'a.cart--link'
    );
}