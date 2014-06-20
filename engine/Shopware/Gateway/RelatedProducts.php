<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:51
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface RelatedProducts
{
    /**
     * Returns an array which contains the order number of
     * each related products for the provided products.
     *
     * Required conditions for the selection:
     * - Selects only main variants of the related products.
     *
     * Example:
     * Provided products:  SW100, SW200
     *
     * Result:
     * array(
     *    'SW100' => array('SW101', 'SW102')
     *    'SW200' => array('SW201', 'SW202')
     * )
     *
     * @param Struct\ListProduct[] $products
     * @return array Indexed by the product number.
     */
    public function getList(array $products);

    /**
     * Returns an array which contains the order number of
     * each related products for the provided product.
     *
     * Example result: array('SW101', 'SW102')
     *
     * @param \Shopware\Struct\ListProduct $product
     * @return array Array of order numbers
     */
    public function get(Struct\ListProduct $product);
}