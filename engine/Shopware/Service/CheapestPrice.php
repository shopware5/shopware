<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway as Gateway;

class CheapestPrice
{
    /**
     * @var \Shopware\Gateway\DBAL\Price
     */
    private $cheapestPriceGateway;

    /**
     * @param Gateway\DBAL\CheapestPrice $cheapestPriceGateway
     */
    function __construct(Gateway\DBAL\CheapestPrice $cheapestPriceGateway)
    {
        $this->cheapestPriceGateway = $cheapestPriceGateway;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Product\PriceRule[]
     */
    public function getList(array $products, Struct\Context $context)
    {
        $specify = $this->cheapestPriceGateway->getList(
            $products,
            $context->getCurrentCustomerGroup()
        );

        $fallback = $this->cheapestPriceGateway->getList(
            $products,
            $context->getFallbackCustomerGroup()
        );

        $prices = array();

        foreach($products as $product) {
            $group = $context->getCurrentCustomerGroup();

            /**@var $cheapestPrice Struct\Product\PriceRule*/
            $cheapestPrice = $specify[$product->getVariantId()];

            if (empty($cheapestPrice)) {
                $group = $context->getFallbackCustomerGroup();
                $cheapestPrice = $fallback[$product->getVariantId()];
            }

            $this->calculatePriceGroupPrice(
                $product,
                $cheapestPrice,
                $context
            );

            $cheapestPrice->setCustomerGroup($group);

            $prices[$product->getId()] = $cheapestPrice;
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
    public function getCheapestPrice(Struct\ListProduct $product, Struct\Context $context)
    {
        $customerGroup = $context->getCurrentCustomerGroup();
        $cheapestPrice = $this->cheapestPriceGateway->get(
            $product, $customerGroup
        );

        if ($cheapestPrice == null) {
            $customerGroup = $context->getFallbackCustomerGroup();
            $cheapestPrice = $this->cheapestPriceGateway->get(
                $product, $customerGroup
            );
        }

        $this->calculatePriceGroupPrice($product, $cheapestPrice, $context);

        $cheapestPrice->setCustomerGroup($customerGroup);

        return $cheapestPrice;
    }

    /**
     * Reduces the passed price with a configured
     * price group discount for the min purchase of the
     * prices unit.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Product\PriceRule $cheapestPrice
     * @param Struct\Context $context
     */
    private function calculatePriceGroupPrice(
        Struct\ListProduct $product,
        Struct\Product\PriceRule $cheapestPrice,
        Struct\Context $context
    ) {

        //check for price group discounts.
        if (!$product->getPriceGroup()) {
            return;
        }

        //selects the highest price group discount, for the passed quantity.
        $discount = $this->cheapestPriceGateway->getPriceGroupDiscount(
            $product->getPriceGroup(),
            $context->getCurrentCustomerGroup(),
            $cheapestPrice->getUnit()->getMinPurchase()
        );

        //check if the discount is numeric, otherwise use a 0 for calculation.
        if (!is_numeric($discount)) {
            $discount = 0;
        }

        $cheapestPrice->setPrice(
            $cheapestPrice->getPrice() / 100 * (100 - $discount)
        );
    }

}