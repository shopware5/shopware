<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: CartPosition
 * Location: Cart positions on cart and checkout confirm page
 *
 * Available retrievable properties:
 * - number (string, e.g. "SW10181")
 * - name (string, e.g. "Reisekoffer Set")
 * - quantity (float, e.g. "1")
 * - itemPrice (float, e.g. "139,99")
 * - sum (float, e.g. "139,99")
 */
class CartPosition extends \Shopware\Tests\Mink\Element\Emotion\CartPosition
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.row--product'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'div.table--content > a.content--title',
            'number' => 'div.table--content > p.content--sku',
            'thumbnailLink' => 'div.table--media a.table--media-link',
            'thumbnailImage' => 'div.table--media a.table--media-link > img',
            'quantity' => 'div.column--quantity option[selected]',
            'itemPrice' => 'div.column--unit-price',
            'sum' => 'div.column--total-price'
        ];
    }
}
