<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway as Gateway;

class Price
{
    /**
     * @var \Shopware\Gateway\DBAL\Price
     */
    private $priceGateway;

    /**
     * @param Gateway\DBAL\Price $priceGateway
     */
    function __construct(Gateway\DBAL\Price $priceGateway)
    {
        $this->priceGateway = $priceGateway;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return \Shopware\Struct\Product\PriceRule[]
     */
    public function getProductPriceList(array $products, Struct\Context $context)
    {
        $specify = $this->priceGateway->getProductPriceList(
            $products,
            $context->getCurrentCustomerGroup()
        );

        $fallback = $this->priceGateway->getProductPriceList(
            $products,
            $context->getFallbackCustomerGroup()
        );

        $prices = array();

        foreach($products as $product) {
            $group = $context->getCurrentCustomerGroup();

            /**@var $productPrices Struct\Product\PriceRule[]*/
            $productPrices = $specify[$product->getVariantId()];

            if (empty($productPrices)) {
                $group = $context->getFallbackCustomerGroup();
                $productPrices = $fallback[$product->getVariantId()];
            }

            foreach($productPrices as $price) {
                $price->setUnit($product->getUnit());
                $price->setCustomerGroup($group);
            }

            $prices[$product->getId()] = $productPrices;
        }

        return $prices;
    }

    /**
     * This function returns the scaled customer group prices for the passed product.
     *
     * The scaled product prices are selected over the s_articles_prices.articledetailsID column.
     * The id is stored in the Struct\ListProduct::variantId property.
     * The prices are ordered ascending by the Struct\Price::from property.
     *
     * @param Struct\ListProduct $product
     * @param \Shopware\Struct\Context $context
     * @return Struct\Product\PriceRule[]
     */
    public function getProductPrices(Struct\ListProduct $product, Struct\Context $context)
    {
        $customerGroup = $context->getCurrentCustomerGroup();
        $prices = $this->priceGateway->getProductPrices(
            $product, $customerGroup
        );

        if (empty($prices)) {
            $customerGroup =  $context->getFallbackCustomerGroup();

            $prices = $this->priceGateway->getProductPrices(
                $product, $customerGroup
            );
        }

        if (empty($prices)) {
            //...
        }

        foreach($prices as $price) {
            $price->setUnit($product->getUnit());
            $price->setCustomerGroup($customerGroup);
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
        $cheapestPrice = $this->priceGateway->getCheapestPrice(
            $product, $customerGroup
        );

        if ($cheapestPrice == null) {
            $customerGroup = $context->getFallbackCustomerGroup();
            $cheapestPrice = $this->priceGateway->getCheapestPrice(
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
        $discount = $this->priceGateway->getPriceGroupDiscount(
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

    /**
     * Calculates all prices of the passed product.
     * The shopware price calculation contains the defined scaled prices and their pseudo prices,
     * reference price, and cheapest price.
     *
     * This function only calculates the gross and net prices. The cheapest price should already
     * set in the product struct.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     */
    public function calculateProduct(Struct\ListProduct $product, Struct\Context $context)
    {
        $tax = $context->getTaxRule($product->getTax()->getId());

        $prices = array();
        foreach($product->getPriceRules() as $rule) {
            $prices[] = $this->calculatePriceStruct(
                $rule,
                $tax,
                $context
            );
        }
        $product->setPrices($prices);

        if ($product->getCheapestPriceRule()) {
            $cheapestPrice = $this->calculatePriceStruct(
                $product->getCheapestPriceRule(),
                $tax,
                $context
            );
            $product->setCheapestPrice($cheapestPrice);
        }

        //add state to the product which can be used to check if the prices are already calculated.
        $product->addState(Struct\ListProduct::STATE_PRICE_CALCULATED);
    }

    /**
     * Helper function which calculates a single price struct of a product.
     * The product can contains multiple price struct elements like the scaled prices
     * and the cheapest price struct.
     * All price structs will be calculated through this function.
     *
     * @param \Shopware\Struct\Product\PriceRule $rule
     * @param \Shopware\Struct\Tax $tax
     * @param Struct\Context $context
     * @return \Shopware\Struct\Product\Price
     */
    private function calculatePriceStruct(
        Struct\Product\PriceRule $rule,
        Struct\Tax $tax,
        Struct\Context $context
    ) {

        $price = new Struct\Product\Price($rule);

        //calculates the normal price of the struct.
        $price->setCalculatedPrice(
            $this->calculatePrice($rule->getPrice(), $tax, $context)
        );

        //check if a pseudo price is defined and calculates it too.
        if ($rule->getPseudoPrice()) {
            $price->setCalculatedPseudoPrice(
                $this->calculatePrice($rule->getPseudoPrice(), $tax, $context)
            );
        }

        //check if the product has unit definitions and calculate the reference price for the unit.
        if ($price->getUnit() && $price->getUnit()->getPurchaseUnit()) {
            $price->setCalculatedReferencePrice(
                $this->calculateReferencePrice($price)
            );
        }

        return $price;
    }


    /**
     * Helper function which calculates a single price value.
     * The function subtracts the percentage customer group discount if
     * it should be considered and decides over the global state if the
     * price should be calculated gross or net.
     * The function is used for the original price value of a price struct
     * and the pseudo price of a price struct.
     *
     * @param $price
     * @param \Shopware\Struct\Tax $tax
     * @param Struct\Context $context
     * @return float
     */
    private function calculatePrice($price, Struct\Tax $tax, Struct\Context $context)
    {
        /**
         * Important:
         * We have to use the current customer group of the current user
         * and not the customer group of the price.
         *
         * The price could be a price of the fallback customer group
         * but the discounts and gross calculation should be used from
         * the current customer group!
         */
        $customerGroup = $context->getCurrentCustomerGroup();

        /**
         * Basket discount calculation:
         *
         * Check if a global basket discount is configured and reduce the price
         * by the percentage discount value of the current customer group.
         */
        if ($customerGroup->useDiscount() && $customerGroup->getPercentageDiscount()) {
            $price = $price - ($price / 100 * $customerGroup->getPercentageDiscount());
        }

        /**
         * Currency calculation:
         * If the customer is currently in a sub shop with another currency, like dollar,
         * we have to calculate the the price for the other currency.
         */
        $price = $price * $context->getCurrency()->getFactor();


        //check if the customer group should see gross prices.
        if (!$customerGroup->displayGrossPrices()) {
            return $price;
        }

        /**
         * Gross calculation:
         *
         * This line contains the gross price calculation within the store front.
         *
         * The passed $context object contains a calculated Struct\Tax object which
         * defines which tax rules should be used for the tax calculation.
         *
         * The tax rules can be defined individual for each customer group and
         * individual for each area, country and state.
         *
         * For example:
         *  - The EK customer group has different configured HIGH-TAX rules.
         *  - In area Europe, in country Germany the global tax value are set to 19%
         *  - But in area Europe, in country Germany, in state Bayern, the tax value are set to 20%
         *  - But in area Europe, in country Germany, in state Berlin, the tax value are set to 18%
         */
        $price = $price * (100 + $tax->getTax()) / 100;

        return $price;
    }

    /**
     * Calculates the product unit reference price for the passed
     * product price.
     *
     * @param Struct\Product\Price $price
     * @return float
     */
    private function calculateReferencePrice(Struct\Product\Price $price)
    {
        return $price->getCalculatedPrice() / $price->getUnit()->getPurchaseUnit() * $price->getUnit()->getReferenceUnit();
    }
}