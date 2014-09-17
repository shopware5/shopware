<?php

namespace Element\Emotion;

class CheckoutShipping extends AccountShipping
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.shipping-address');

    /** @var array $cssLocator */
    public $cssLocator = array(
        'currentMethod' => 'p'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'changeButton'  => array('de' => 'Ändern',       'en' => 'Change')
    );
}
