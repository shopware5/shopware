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
interface ListProduct
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\ListProduct::get()
     *
     * @param array $numbers
     * @param \Shopware\Struct\Context $context
     * @return Struct\ListProduct[] Indexed by the product order number
     */
    public function getList(array $numbers, Struct\Context $context);

    /**
     * The \Shopware\Struct\ListProduct requires the following data:
     * - Product base data
     * - Variant data of the passed number
     * - Unit data of the variant
     * - Price group
     * - Tax data
     * - Manufacturer data
     * - Esd data of the variant
     * - Sales of the product
     * - Core attribute of the variant
     * - Core attribute of the manufacturer
     * - Core attribute of the esd
     *
     * Required translation in the provided context language:
     * - Product
     * - Variant
     * - Manufacturer
     * - Unit of the variant
     *
     * @param $number
     * @param \Shopware\Struct\Context $context
     * @return Struct\ListProduct
     */
    public function get($number, Struct\Context $context);
}