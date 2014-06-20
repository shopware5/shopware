<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:55
 */
namespace Shopware\Service;

use Shopware\Struct;

interface GraduatedPrices
{
    /**
     * @see \Shopware\Service\Core\GraduatedPrices::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array Indexed by the product number, each array element contains a Struct\Product\PriceRule array.
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * Returns the graduated product prices for the provided context and product.
     *
     * If the current customer group has no specified prices, the function returns
     * the graduated product prices for the fallback customer group.
     *
     * In case that the product has an assigned price group, the graduated prices are build by the
     * price group discounts definition.
     *
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\GraduatedPrices::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Product\PriceRule[]
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);
}
