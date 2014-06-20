<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:44
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface CheapestPrice
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\CheapestPrice::get()
     *
     * @param Struct\ListProduct[] $products
     * @param \Shopware\Struct\Context $context
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule[] Indexed by the product id
     */
    public function getList(array $products, Struct\Context $context, Struct\Customer\Group $customerGroup);

    /**
     * The cheapest product price is only selected for the provided customer group.
     *
     * If the provided customer group has no defined price, the function has to return null.
     *
     * The \Shopware\Struct\Product\PriceRule requires the following data:
     * - The price data
     * - Core attribute of the price
     * - Variant unit of the selected cheapest price.
     *
     * Required translation in the provided context language:
     * - Variant unit data.
     *
     * Required conditions for the selection:
     *  - Only the first graduated price
     *  - Cheapest price is selected between all variants of the product
     *  - Cheapest price requires the selected variant unit for base price calculation
     *  - The variants has to be active
     *  - Closeout variants can only be selected if the stock > min purchase
     *
     * @param \Shopware\Struct\ListProduct $product
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule
     */
    public function get(Struct\ListProduct $product, Struct\Customer\Group $customerGroup);
}