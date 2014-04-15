<?php

namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ResponseTextException;

class HeaderCart extends Element
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#shopnavi');

    public $cssLocator = array(
        'quantity' => 'a.quantity',
        'amount' => 'span.amount',
        'link' => 'a.quantity'
    );

    /**
     * @param string $keywords
     *
     * @return Page
     */
    public function checkCart($quantity, $amount)
    {
        $locators = array('quantity', 'amount');
        $element = \Helper::findElements($this, $locators);

        $check = array(
            'quantity' => array($element['quantity']->getText(), $quantity),
            'amount' => \Helper::toFloat(array($element['amount']->getText(), $amount))
        );

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                'The %s of the header cart is wrong! (%s instead of %s)',
                $result, $check[$result][0], $check[$result][1]
            );
            throw new ResponseTextException($message, $this->getSession());
        }
    }

    public function clickCart()
    {
        $locators = array('link');
        $element = \Helper::findElements($this, $locators);

        $element['link']->click();
    }
}