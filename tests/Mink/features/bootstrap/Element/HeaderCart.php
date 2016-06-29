<?php

namespace Shopware\Tests\Mink\Element;

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
    protected $selector = ['css' => 'li.navigation--entry.entry--cart'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'quantity' => 'span.cart--quantity',
            'amount' => 'span.cart--amount',
            'link' => 'a.cart--link'
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
            'quantity' => array(Helper::intValue($element['quantity']->getText()), $quantity),
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
