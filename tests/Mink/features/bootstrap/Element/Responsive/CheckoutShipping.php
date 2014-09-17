<?php

namespace Element\Responsive;

class CheckoutShipping extends \Element\Emotion\CheckoutShipping
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.panel--group > div.panel:nth-of-type(2)');
}
