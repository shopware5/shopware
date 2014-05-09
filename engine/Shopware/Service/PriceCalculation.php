<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway\DBAL as Gateway;

class PriceCalculation
{
    /**
     * @var Gateway\PriceGroupDiscount
     */
    private $priceGroupDiscountGateway;

    /**
     * @param $priceGroupDiscountGateway
     */
    function __construct($priceGroupDiscountGateway)
    {
        $this->priceGroupDiscountGateway = $priceGroupDiscountGateway;
    }

    /**
     * Calculates all prices of the passed product.
     * The shopware price calculation contains the defined graduated prices and their pseudo prices,
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
        foreach ($product->getPriceRules() as $rule) {
            $prices[] = $this->calculatePriceStruct(
                $rule,
                $tax,
                $context
            );
        }

        $product->setPrices($prices);

        if ($product->getCheapestPriceRule()) {
            $cheapestPrice = $this->calculateCheapestPrice(
                $product,
                $product->getCheapestPriceRule(),
                $context
            );

            $product->setCheapestPrice($cheapestPrice);
        }

        //add state to the product which can be used to check if the prices are already calculated.
        $product->addState(Struct\ListProduct::STATE_PRICE_CALCULATED);
    }


    /**
     * Reduces the passed price with a configured
     * price group discount for the min purchase of the
     * prices unit.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Product\PriceRule $cheapestPrice
     * @param Struct\Context $context
     * @return \Shopware\Struct\Product\Price
     */
    private function calculateCheapestPrice(
        Struct\ListProduct $product,
        Struct\Product\PriceRule $cheapestPrice,
        Struct\Context $context
    ) {

        $tax = $context->getTaxRule($product->getTax()->getId());

        $cheapestPrice->setPrice(
            $cheapestPrice->getUnit()->getMinPurchase() * $cheapestPrice->getPrice()
        );

        $cheapestPrice->setPseudoPrice(
            $cheapestPrice->getUnit()->getMinPurchase() * $cheapestPrice->getPseudoPrice()
        );

        //check for price group discounts.
        if (!$product->getPriceGroup()) {
            return $this->calculatePriceStruct($cheapestPrice, $tax, $context);
        }

        //selects the highest price group discount, for the passed quantity.
        $discount = $this->priceGroupDiscountGateway->getHighestQuantityDiscount(
            $product->getPriceGroup(),
            $context->getCurrentCustomerGroup(),
            $cheapestPrice->getUnit()->getMinPurchase()
        );

        $cheapestPrice->setPrice(
            $cheapestPrice->getPrice() / 100 * (100 - $discount->getPercent())
        );

        return $this->calculatePriceStruct(
            $cheapestPrice,
            $tax,
            $context
        );
    }

    /**
     * Helper function which calculates a single price struct of a product.
     * The product can contains multiple price struct elements like the graduated prices
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
        $price->setCalculatedPseudoPrice(
            $this->calculatePrice($rule->getPseudoPrice(), $tax, $context)
        );

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