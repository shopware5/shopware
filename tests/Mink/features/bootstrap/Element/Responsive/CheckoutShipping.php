<?php

namespace Responsive;

class CheckoutShipping extends \Emotion\CheckoutShipping
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.panel--group > div.panel:nth-of-type(2)');
}
