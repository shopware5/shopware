<?php

namespace Shopware\Gateway;

use Shopware\Struct as Struct;

interface Price
{
    /**
     * This function returns the scaled customer group prices for the passed product.
     *
     * The scaled product prices are selected over the s_articles_prices.articledetailsID column.
     * The id is stored in the Struct\ListProduct::variantId property.
     * Additionally it is important that the prices are ordered ascending by the Struct\Price::from property.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule[]
     */
    public function getProductPrices(
        Struct\ListProduct $product,
        Struct\Customer\Group $customerGroup
    );

    /**
     * Returns the cheapest product price struct for the passed customer group.
     *
     * The cheapest product price is selected over all product variations.
     *
     * This means that the query uses the s_articles_prices.articleID column for the where condition.
     * The articleID is stored in the Struct\ListProduct::id property.
     *
     * It is important that the cheapest price contains the associated product Struct\Unit of the
     * associated product variation.
     *
     * For example:
     *  - Current product variation is the SW2000
     *    - This product variation contains no associated Struct\Unit
     *  - The cheapest variant price is associated to the SW2000.2
     *    - This product variation contains an associated Struct\Unit
     *  - The unit of SW2000.2 has to be set into the Struct\Price::unit property!
     *
     * @param Struct\ListProduct $product
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule
     */
    public function getCheapestPrice(
        Struct\ListProduct $product,
        Struct\Customer\Group $customerGroup
    );


    /**
     * Returns the highest percentage discount for the
     * customer group of the passed price group and quantity.
     *
     * @param Struct\Product\PriceGroup $priceGroup
     * @param Struct\Customer\Group $customerGroup
     * @param $quantity
     * @return int
     */
    public function getPriceGroupDiscount(
        Struct\Product\PriceGroup $priceGroup,
        Struct\Customer\Group $customerGroup,
        $quantity
    );
}