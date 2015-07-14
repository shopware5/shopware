<?php

namespace Element\Responsive;

/**
 * Element: HeaderCart
 * Location: Cart on the top right of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class HeaderCart extends \Element\Emotion\HeaderCart
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'li.navigation--entry.entry--cart'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
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
