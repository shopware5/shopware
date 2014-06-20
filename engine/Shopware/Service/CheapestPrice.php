<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:55
 */
namespace Shopware\Service;

use Shopware\Struct;

interface CheapestPrice
{
    /**
     * @see \Shopware\Service\Core\CheapestPrice::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Product\PriceRule[] Indexed by product number
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * Returns the cheapest product price for the provided context and product.
     *
     * If the current customer group has no specified prices, the function returns
     * the cheapest product price for the fallback customer group.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\CheapestPrice::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Product\PriceRule
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);
}