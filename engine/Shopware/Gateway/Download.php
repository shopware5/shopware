<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:46
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface Download
{
    /**
     * To get detailed information about the structure and content of the returned object,
     * please refer to the @see \Shopware\Gateway\DBAL\Download::get()
     *
     * @param Struct\ListProduct[] $products
     * @param \Shopware\Struct\Context $context
     * @return array Indexed by the product number, each array element contains a \Shopware\Struct\Product\Download array.
     */
    public function getList(array $products, Struct\Context $context);

    /**
     * The \Shopware\Struct\Product\Download requires the following data:
     * - Download base data
     * - Core attribute of the download
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Product\Download[]
     */
    public function get(Struct\ListProduct $product, Struct\Context $context);
}