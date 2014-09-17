<?php

namespace Element\Emotion;

class CheckoutPayment extends AccountPayment
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.payment-display');

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'changeButton'  => array('de' => 'Ändern',       'en' => 'Change')
    );
}
