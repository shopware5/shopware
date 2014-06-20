<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:46
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface GraduatedPrices
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\GraduatedPrices::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule[]
     */
    public function getList(array $products, Struct\Customer\Group $customerGroup);

    /**
     * The \Struct\Product\PriceRule requires the following data:
     * - Price base data
     * - Core attribute of the price
     *
     * Required conditions for the selection:
     * - Sorted ascending with the \Shopware\Struct\Product\PriceRule::from property.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule[]
     */
    public function get(Struct\ListProduct $product, Struct\Customer\Group $customerGroup);
}