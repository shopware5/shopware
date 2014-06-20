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
interface VoteAverage
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\VoteAverage::get()
     *
     * @param Struct\ListProduct[] $products
     * @return \Shopware\Struct\Product\VoteAverage Indexed by the product order number
     */
    public function getList(array $products);

    /**
     * The \Shopware\Struct\VoteAverage requires the following data:
     * - Total count of votes
     * - Count for each point
     *
     * Required conditions for the selection:
     * - Only activated votes
     *
     * @param Struct\ListProduct $product
     * @return \Shopware\Struct\Product\VoteAverage
     */
    public function get(Struct\ListProduct $product);
}