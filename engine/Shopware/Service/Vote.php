<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:57
 */
namespace Shopware\Service;

use Shopware\Struct;

interface Vote
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\VoteAverage::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Product\VoteAverage
     */
    public function getAverage(Struct\ListProduct $product, Struct\Context $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Vote::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     *
     * @return array Indexed by the product order number, each array element contains a \Shopware\Struct\Vote array.
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Vote::get()
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Product\Vote[]
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\VoteAverage::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     *
     * @return Struct\Product\VoteAverage[] Indexed by the product order number
     */
    public function getAverages(array $products, Struct\Context $context);
}