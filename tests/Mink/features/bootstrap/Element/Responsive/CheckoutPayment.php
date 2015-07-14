<?php

namespace Element\Responsive;

/**
 * Element: CheckoutPayment
 * Location: Payment box on checkout confirm page
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class CheckoutPayment extends \Element\Emotion\CheckoutPayment
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.payment--panel'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'currentMethod' => 'span.payment--description'
        ];
    }

    public function getPaymentMethodProperty()
    {
        $element = \Helper::findElements($this, ['currentMethod']);

        return $element['currentMethod']->getText();
    }
}
