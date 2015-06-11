<?php

namespace Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

class HeaderCart extends Element implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#shopnavi');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'quantity' => 'a.quantity',
            'amount' => 'span.amount',
            'link' => 'a.quantity'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array();
    }

    /**
     * @param string $quantity
     * @param float $amount
     * @throws \Exception
     */
    public function checkCart($quantity, $amount)
    {
        $locators = array('quantity', 'amount');
        $element = \Helper::findElements($this, $locators);

        $check = array(
            'quantity' => array($element['quantity']->getText(), $quantity),
            'amount' => \Helper::floatArray(array($element['amount']->getText(), $amount))
        );

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                'The %s of the header cart is wrong! (%s instead of %s)',
                $result, $check[$result][0], $check[$result][1]
            );
            \Helper::throwException($message);
        }
    }

    /**
     *
     */
    public function clickCart()
    {
        $locators = array('link');
        $element = \Helper::findElements($this, $locators);

        $element['link']->click();
    }
}
