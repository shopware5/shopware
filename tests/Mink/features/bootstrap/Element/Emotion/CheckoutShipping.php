<?php

namespace Element\Emotion;

class CheckoutShipping extends AccountShipping
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.shipping-address');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'currentMethod' => 'p'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'changeButton'  => array('de' => 'Ändern', 'en' => 'Change'),
            'otherButton'  => array('de' => 'Andere', 'en' => 'Change')
        );
    }
}
