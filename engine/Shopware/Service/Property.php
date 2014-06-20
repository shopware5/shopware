<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:56
 */
namespace Shopware\Service;

use Shopware\Struct;


/**
 * @package Shopware\Service
 */
interface Property
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Service\Property::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Property\Set[]
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\ProductProperty::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Property\Set
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);
}
