<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:48
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface Product
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\ListProduct::get()
     *
     * @param array $numbers
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product[] Indexed by the product order number
     */
    public function getList(array $numbers, Struct\Context $context);

    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\ListProduct::get()
     *
     * @param $number
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product
     */
    public function get($number, Struct\Context $context);
}