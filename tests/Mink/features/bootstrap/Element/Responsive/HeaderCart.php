<?php

namespace Responsive;

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
