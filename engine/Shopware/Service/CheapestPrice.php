<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway\DBAL as Gateway;

class CheapestPrice
{
    /**
     * @var Gateway\CheapestPrice
     */
    private $cheapestPriceGateway;

    /**
     * @var GraduatedPrices
     */
    private $graduatedPricesService;

    /**
     * @param Gateway\CheapestPrice $cheapestPriceGateway
     * @param GraduatedPrices $graduatedPricesService
     */
    function __construct(
        Gateway\CheapestPrice $cheapestPriceGateway,
        GraduatedPrices $graduatedPricesService
    ) {
        $this->cheapestPriceGateway = $cheapestPriceGateway;
        $this->graduatedPricesService = $graduatedPricesService;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Product\PriceRule[]
     */
    public function getList(array $products, Struct\Context $context)
    {
        $group = $context->getCurrentCustomerGroup();

        $specify = $this->cheapestPriceGateway->getList($products, $group);

        $prices = array();

        $fallback = array();
        foreach ($products as $product) {
            $key = $product->getId();

            /**@var $cheapestPrice Struct\Product\PriceRule */
            $cheapestPrice = $specify[$key];

            if (empty($cheapestPrice)) {
                $fallback[] = $product;
                continue;
            }

            $cheapestPrice->setCustomerGroup($group);

            $prices[$key] = $cheapestPrice;
        }

        if (empty($fallback)) {
            return $prices;
        }

        //fallback prices for the default customer group of the shop.
        $group = $context->getFallbackCustomerGroup();

        //fallback array contains at this point a list of product structs
        $fallback = $this->cheapestPriceGateway->getList($fallback, $group);

        foreach ($products as $product) {
            $key = $product->getId();

            /**@var $cheapestPrice Struct\Product\PriceRule */
            $cheapestPrice = $fallback[$key];

            if (empty($cheapestPrice)) {
                continue;
            }

            $cheapestPrice->setCustomerGroup($group);

            $prices[$key] = $cheapestPrice;
        }

        return $prices;
    }

    /**
     * Returns the cheapest product price struct.
     *
     * The cheapest product price is selected over all product variations.
     *
     * This means that the query uses the s_articles_prices.articleID column for the where condition.
     * The articleID is stored in the Struct\ListProduct::id property.
     *
     * The cheapest price contains the associated product Struct\Unit of the associated product variation.
     * This means:
     *  - Current product variation is the SW2000
     *    - This product variation contains no associated Struct\Unit
     *  - The cheapest variant price is associated to the SW2000.2
     *    - This product variation contains an associated Struct\Unit
     *  - The unit of SW2000.2 is set into the Struct\Price::unit property
     *
     * @param Struct\ListProduct $product
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product\PriceRule
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $cheapestPrices = $this->getList(array($product), $context);

        return array_shift($cheapestPrices);
    }
}