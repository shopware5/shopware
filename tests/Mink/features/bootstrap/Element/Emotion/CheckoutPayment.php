<?php

namespace Element\Emotion;

class CheckoutPayment extends AccountPayment implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.payment-display');

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'changeButton' => array('de' => 'Ändern', 'en' => 'Change')
        );
    }
}
