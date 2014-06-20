<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:56
 */
namespace Shopware\Service;

use Shopware\Struct;

interface Product
{
    /**
     * @see \Shopware\Service\Product::get()
     *
     * @param $numbers
     * @param Struct\Context $context
     * @return Struct\Product[] Indexed by the product order number
     */
    public function getList($numbers, Struct\Context $context);

    /**
     * Returns a full \Shopware\Struct\Product object which all required data.
     *
     * A full \Shopware\Struct\Product is build over the following classes:
     * - \Shopware\Gateway\Product
     * - \Shopware\Service\Media
     * - \Shopware\Service\GraduatedPrices
     * - \Shopware\Service\Vote
     * - \Shopware\Service\RelatedProducts
     * - \Shopware\Service\SimilarProducts
     * - \Shopware\Service\ProductDownload
     * - \Shopware\Service\ProductLink
     * - \Shopware\Service\Property
     * - \Shopware\Service\Configurator
     * - \Shopware\Service\CheapestPrice
     * - \Shopware\Service\Marketing
     *
     * The different services selects the specify product associated data
     * for the provided product.
     *
     * The function injects the different sources into the \Shopware\Struct\Product class
     * and calculates the prices for the store front through a \Shopware\Service\PriceCalculation class.
     *
     * @param $number
     * @param Struct\Context $context
     * @return Struct\Product
     */
    public function get($number, Struct\Context $context);
}
