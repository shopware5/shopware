<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;

/**
 * Element: HeaderCart
 * Location: Cart on the top right of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class HeaderCart extends Element implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div#shopnavi');

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'quantity' => 'a.quantity',
            'amount' => 'span.amount',
            'link' => 'a.quantity'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     *
     * @param string $quantity
     * @param float $amount
     * @throws \Exception
     */
    public function checkCart($quantity, $amount)
    {
        $element = Helper::findElements($this, ['quantity', 'amount']);

        $check = array(
            'quantity' => array($element['quantity']->getText(), $quantity),
            'amount' => Helper::floatArray(array($element['amount']->getText(), $amount))
        );

        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                'The %s of the header cart is wrong! (%s instead of %s)',
                $result, $check[$result][0], $check[$result][1]
            );
            Helper::throwException($message);
        }
    }

    /**
     *
     */
    public function clickCart()
    {
        $element = Helper::findElements($this, 'link');

        $element['link']->click();
    }
}
