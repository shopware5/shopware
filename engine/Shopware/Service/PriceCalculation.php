<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:56
 */
namespace Shopware\Service;

use Shopware\Struct;

interface PriceCalculation
{
    /**
     * Calculates all prices of the product.
     *
     * The product contains two different prices, the graduated prices and the cheapest price.
     * Each price type contains a single or multiple \Shopware\Struct\Product\PriceRule elements.
     *
     * Each price rule contains a price, pseudo price and a reference price which calculates over
     * the assigned price unit based on the original price.
     *
     * The calculated \Shopware\Struct\Product\PriceRule structs are wrapped into a \Shopware\Struct\Product\Price
     * struct which contains only the calculated price values and the reference to his rule.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     */
    public function calculateProduct(Struct\ListProduct $product, Struct\Context $context);
}