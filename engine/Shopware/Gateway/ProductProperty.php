<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:50
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface ProductProperty
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\ProductProperty::get()
     *
     * @param Struct\Product[] $products
     * @param \Shopware\Struct\Context $context
     * @return Struct\Property\Set[] Indexed by the product order number
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * The \Shopware\Struct\Property\Set requires the following data:
     * - Property set data
     * - Property groups data
     * - Property options data
     * - Core attribute of the property set
     *
     * Required translation in the provided context language:
     * - Property set
     * - Property groups
     * - Property options
     *
     * Required conditions for the selection:
     * - Selects only values which assigned to the provided products
     * - Property values has to be sorted by the \Shopware\Struct\Property\Set sort mode.
     * - Sort mode equals to 1, the values are sorted by the numeric value
     * - Sort mode equals to 3, the values are sorted by the position
     * - In all other cases the values are sorted by their alphanumeric value
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Property\Set
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);
}