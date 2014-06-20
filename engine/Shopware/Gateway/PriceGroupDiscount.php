<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:47
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface PriceGroupDiscount
{
    /**
     * The \Shopware\Struct\Product\PriceDiscount requires the following data:
     * - Price group discount base data
     *
     * Required conditions for the selection:
     * - \Shopware\Struct\Product\PriceDiscount::quantity is less or equals the provided quantity.
     * - Selects only discounts for the passed customer group
     * - Selects the discount with the highest value
     *
     * @param Struct\Product\PriceGroup $priceGroup
     * @param Struct\Customer\Group $customerGroup
     * @param $quantity
     * @return \Shopware\Struct\Product\PriceDiscount
     */
    public function getHighestQuantityDiscount(
        Struct\Product\PriceGroup $priceGroup,
        Struct\Customer\Group $customerGroup,
        $quantity
    );

    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\PriceGroupDiscount::getProductDiscount()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Customer\Group $customerGroup
     * @param \Shopware\Struct\Context $context
     * @return array Indexed by the product number. Each element contains a Struct\Product\PriceDiscount array.
     */
    public function getProductsDiscounts(
        array $products,
        Struct\Customer\Group $customerGroup,
        Struct\Context $context
    );

    /**
     * The \Shopware\Struct\Product\PriceDiscount requires the following data:
     * - Price group discount base data
     *
     * Required conditions for the selection:
     * - Sorted ascending by the \Shopware\Struct\Product\PriceDiscount::quantity property.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Customer\Group $customerGroup
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product\PriceDiscount[]
     */
    public function getProductDiscount(
        Struct\ListProduct $product,
        Struct\Customer\Group $customerGroup,
        Struct\Context $context
    );
}