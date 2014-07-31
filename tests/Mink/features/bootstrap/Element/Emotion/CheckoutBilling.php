<?php

namespace Emotion;

class CheckoutBilling extends AccountBilling
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.invoice-address');

    /** @var array $cssLocator */
    public $cssLocator = array(
        'currentMethod' => 'p'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'changeButton'  => array('de' => 'Ändern',       'en' => 'Change')
    );
}
