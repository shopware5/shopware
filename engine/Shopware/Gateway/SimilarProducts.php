<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:52
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface SimilarProducts
{
    /**
     * Returns an array which contains the order number of
     * each similar products for the provided products.
     *
     * Required conditions for the selection:
     * - Selects only main variants of the similar products.
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
     * @param \Shopware\Struct\Context $context
     * @return array Indexed by the product number
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * Returns an array which contains the order number of
     * each related products for the provided product.
     *
     * Example result: array('SW101', 'SW102')
     *
     * @param \Shopware\Struct\ListProduct $product
     * @param \Shopware\Struct\Context $context
     * @return array Array of order numbers
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);

    /**
     * Returns an array which contains the order number of
     * each similar products for the provided product.
     *
     * Required conditions for the selection:
     * - Selects only main variants of the similar products.
     * - Selects products which are in the same category
     *
     * Example result: array('SW101', 'SW102')
     *
     * @param \Shopware\Struct\ListProduct $product
     * @param \Shopware\Struct\Context $context
     * @return array Array of order numbers
     */
    public function getByCategory(Struct\ListProduct $product, Struct\Context $context);

    /**
     * Returns an array which contains the order number of
     * each similar products for the provided products.
     *
     * Required conditions for the selection:
     * - Selects only main variants of the similar products.
     * - Selects products which are in the same category
     *
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
     * @param \Shopware\Struct\Context $context
     * @return array Indexed by the product number
     */
    public function getByListCategory(array $products, Struct\Context $context);
}