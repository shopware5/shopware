<?php

namespace Responsive;

class CheckoutBilling extends \Emotion\CheckoutBilling
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.panel--group > div.panel:nth-of-type(1)');
}
