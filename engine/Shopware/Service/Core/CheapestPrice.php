<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

class CheapestPrice implements Service\CheapestPrice
{
    /**
     * @var Gateway\CheapestPrice
     */
    private $cheapestPriceGateway;

    /**
     * @param Gateway\CheapestPrice $cheapestPriceGateway
     */
    function __construct(Gateway\CheapestPrice $cheapestPriceGateway)
    {
        $this->cheapestPriceGateway = $cheapestPriceGateway;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $cheapestPrices = $this->getList(array($product), $context);

        return array_shift($cheapestPrices);
    }

    /**
     * @inheritdoc
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
     * Helper function which iterates the products and builds a price array which indexed
     * with the product order number.
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Product\PriceRule[] $priceRules
     * @param Struct\Customer\Group $group
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
