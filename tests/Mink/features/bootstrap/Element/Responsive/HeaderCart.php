<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: HeaderCart
 * Location: Cart on the top right of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class HeaderCart extends \Shopware\Tests\Mink\Element\Emotion\HeaderCart
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
}
