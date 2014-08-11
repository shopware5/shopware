<?php

namespace Responsive;

class CheckoutPayment extends \Emotion\CheckoutPayment
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.panel--group > div.panel:nth-of-type(3)');
}
