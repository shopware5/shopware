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
interface Link
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\Link::get()
     *
     * @param Struct\ListProduct[] $products
     * @return array Indexed by the product order number. Each element contains a \Shopware\Struct\Product\Link array
     */
    public function getList(array $products);

    /**
     * The \Shopware\Struct\Product\Link requires the following data:
     * - Link base data
     * - Core attribute of the link
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return \Shopware\Struct\Product\Link[]
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);
}