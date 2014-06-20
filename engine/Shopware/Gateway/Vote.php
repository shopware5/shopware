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
interface Vote
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\Vote::get()
     *
     * @param Struct\ListProduct[] $products
     * @return array Indexed by the product number. Each elements contains a Struct\Product\Vote array.
     */
    public function getList(array $products);

    /**
     * The \Shopware\Struct\Vote requires the following data:
     * - Vote base data
     *
     * Required conditions for the selection:
     * - Sorted by the vote create date
     *
     * @param Struct\ListProduct $product
     * @return Struct\Product\Vote
     */
    public function get(Struct\ListProduct $product);
}