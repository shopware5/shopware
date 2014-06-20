<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:56
 */
namespace Shopware\Service;

use Shopware\Struct;

interface ProductLink
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Service\ProductLink::get()
     *
     * @param array $products
     * @param Struct\Context $context
     * @return array Indexed by the product order number, each array element contains a list of \Shopware\Struct\Product\Link classes.
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Link::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return mixed
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);
}