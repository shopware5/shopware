<?php

namespace Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Element: CheckoutPayment
 * Location: Payment box on checkout confirm page
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class CheckoutPayment extends Element implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.payment-display');

    /**
     * Returns an array of all css selectors of the element/page
     * @return string[]
     */
    public function getCssSelectors()
    {
        return [
            'currentMethod' => 'p'
        ];
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array[]
     */
    public function getNamedSelectors()
    {
        return [
            'changeButton' => ['de' => 'Ändern', 'en' => 'Change']
        ];
    }

    /**
     * Returns the current payment method
     * @return string
     */
    public function getPaymentMethodProperty()
    {
        $element = \Helper::findElements($this, ['currentMethod']);

        $currentMethod = $element['currentMethod']->getText();
        $currentMethod = str_word_count($currentMethod, 1);

        return $currentMethod[0];
    }
}
