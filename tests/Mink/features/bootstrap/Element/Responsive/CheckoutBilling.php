<?php

namespace Element\Responsive;

class CheckoutBilling extends \Element\Emotion\CheckoutBilling
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.panel--group > div.panel:nth-of-type(1)');
}
