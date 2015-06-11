<?php

namespace Element\Responsive;

class CartPosition extends \Element\Emotion\CartPosition
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.row--product');

    public function getCssSelectors()
    {
        return array(
            'name' => 'div.table--content > a.content--title',
            'number' => 'div.table--content > p.content--sku',
            'thumbnailLink' => 'div.table--media a.table--media-link',
            'thumbnailImage' => 'div.table--media a.table--media-link > img',
            'quantity' => 'div.column--quantity option[selected]',
            'itemPrice' => 'div.column--unit-price',
            'sum' => 'div.column--total-price'
        );
    }
}
