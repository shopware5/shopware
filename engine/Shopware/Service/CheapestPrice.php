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
     * Returns the cheapest product price structs for all passed products.
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
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Product\PriceRule[] Indexed by product number
     */
    public function getList(array $products, Struct\Context $context)
    {
        $group = $context->getCurrentCustomerGroup();

        $rules = $this->cheapestPriceGateway->getList($products, $context, $group);

        $prices = $this->buildPrices($products, $rules, $group);

        //check if one of the products have no assigned price within the prices variable.
        $fallbackProducts = array_filter(
            $products,
            function (Struct\ListProduct $product) use ($prices) {
                return !array_key_exists($product->getNumber(), $prices);
            }
        );

        if (empty($fallbackProducts)) {
            return $prices;
        }

        //if some product has no price, we have to load the fallback customer group prices for the fallbackProducts.
        $fallbackPrices = $this->cheapestPriceGateway->getList(
            $fallbackProducts,
            $context,
            $context->getFallbackCustomerGroup()
        );

        $fallbackPrices = $this->buildPrices(
            $fallbackProducts,
            $fallbackPrices,
            $context->getFallbackCustomerGroup()
        );

        return array_merge($prices, $fallbackPrices);
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

    /**
     * Helper function which iterates the products and builds a price array which indexed
     * with the product order number.
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Product\PriceRule[] $priceRules
     * @param \Shopware\Struct\Customer\Group $group
     * @return array
     */
    private function buildPrices(array $products, array $priceRules, Struct\Customer\Group $group)
    {
        $prices = array();

        foreach ($products as $product) {
            $key = $product->getId();

            if (!array_key_exists($key, $priceRules) || empty($priceRules[$key])) {
                continue;
            }

            /**@var $cheapestPrice Struct\Product\PriceRule */
            $cheapestPrice = $priceRules[$key];

            $cheapestPrice->setCustomerGroup($group);

            $prices[$product->getNumber()] = $cheapestPrice;
        }

        return $prices;
    }
}
