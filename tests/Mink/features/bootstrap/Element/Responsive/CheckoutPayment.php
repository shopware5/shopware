<?php

namespace Element\Responsive;

class CheckoutPayment extends \Element\Emotion\CheckoutPayment
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.payment--panel');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'currentMethod' => 'span.payment--description'
        );
    }

    public function getPaymentMethodProperty()
    {
        $locators = array('currentMethod');
        $element = \Helper::findElements($this, $locators);

        return $element['currentMethod']->getText();
    }
}
