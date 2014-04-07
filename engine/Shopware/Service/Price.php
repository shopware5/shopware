<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;
use Shopware\Gateway as Gateway;

class Price
{
    /**
     * @var \Shopware\Gateway\Price
     */
    private $priceGateway;

    /**
     * @param Gateway\Price $priceGateway
     */
    function __construct(Gateway\Price $priceGateway)
    {
        $this->priceGateway = $priceGateway;
    }

    /**
     * This function returns the scaled customer group prices for the passed product.
     *
     * The scaled product prices are selected over the s_articles_prices.articledetailsID column.
     * The id is stored in the Struct\ProductMini::variantId property.
     * The prices are ordered ascending by the Struct\Price::from property.
     *
     * @param Struct\ProductMini $product
     * @param \Shopware\Struct\GlobalState $state
     * @return Struct\Price[]
     */
    public function getProductPrices(Struct\ProductMini $product, Struct\GlobalState $state)
    {
        $customerGroup = $state->getCurrentCustomerGroup();

        $prices = $this->priceGateway->getProductPrices(
            $product, $customerGroup
        );

        if (empty($prices)) {
            $customerGroup =  $state->getFallbackCustomerGroup();

            $prices = $this->priceGateway->getProductPrices(
                $product, $customerGroup
            );
        }

        foreach($prices as $price) {
            $price->setUnit($product->getUnit());
            $price->setCustomerGroup($customerGroup);
        }

        return $prices;
    }

    /**
     * Helper function which iterates the passed prices array
     * and returns the cheapest price.
     *
     * @param \Shopware\Struct\ProductMini $product
     * @return \Shopware\Struct\Price
     */
    public function getCheapestVariantPrice(Struct\ProductMini $product)
    {
        $cheapestPrice = null;

        foreach($product->getPrices() as $price) {

            if ($cheapestPrice === null) {
                $cheapestPrice = $price;
            }

            if ($price->getCalculatedPrice() < $cheapestPrice->getCalculatedPrice()) {
                $cheapestPrice = $price;
            }
        }
        return $cheapestPrice;
    }

    /**
     * Returns the cheapest product price struct.
     *
     * The cheapest product price is selected over all product variations.
     *
     * This means that the query uses the s_articles_prices.articleID column for the where condition.
     * The articleID is stored in the Struct\ProductMini::id property.
     *
     * The cheapest price contains the associated product Struct\Unit of the associated product variation.
     * This means:
     *  - Current product variation is the SW2000
     *    - This product variation contains no associated Struct\Unit
     *  - The cheapest variant price is associated to the SW2000.2
     *    - This product variation contains an associated Struct\Unit
     *  - The unit of SW2000.2 is set into the Struct\Price::unit property
     *
     * @param Struct\ProductMini $product
     * @param \Shopware\Struct\GlobalState $state
     * @return Struct\Price
     */
    public function getCheapestProductPrice(Struct\ProductMini $product, Struct\GlobalState $state)
    {
        $customerGroup = $state->getCurrentCustomerGroup();
        $cheapestPrice = $this->priceGateway->getCheapestProductPrice(
            $product, $customerGroup
        );

        if ($cheapestPrice == null) {
            $customerGroup = $state->getFallbackCustomerGroup();
            $cheapestPrice = $this->priceGateway->getCheapestProductPrice(
                $product, $customerGroup
            );
        }

        $cheapestPrice->setCustomerGroup($customerGroup);

        return $cheapestPrice;
    }

    /**
     * Calculates all prices of the passed product.
     * The shopware price calculation contains the defined scaled prices and their pseudo prices,
     * reference price, and cheapest price.
     *
     * This function only calculates the gross and net prices. The cheapest price should already
     * set in the product struct.
     *
     * @param Struct\ProductMini $product
     * @param Struct\GlobalState $state
     */
    public function calculateProduct(Struct\ProductMini $product, Struct\GlobalState $state)
    {
        foreach($product->getPrices() as $price) {
            $this->calculatePriceStruct(
                $price,
                $state
            );
        }

        if ($product->getCheapestProductPrice()) {
            $this->calculatePriceStruct(
                $product->getCheapestProductPrice(),
                $state
            );
        }

        //add state to the product which can be used to check if the prices are already calculated.
        $product->addState(Struct\ProductMini::STATE_PRICE_CALCULATED);
    }

    /**
     * Helper function which calculates a single price struct of a product.
     * The product can contains multiple price struct elements like the scaled prices
     * and the cheapest price struct.
     * All price structs will be calculated through this function.
     *
     * @param Struct\Price $price
     * @param Struct\GlobalState $state
     */
    private function calculatePriceStruct(
        Struct\Price $price,
        Struct\GlobalState $state
    ) {

        //calculates the normal price of the struct.
        $price->setCalculatedPrice(
            $this->calculatePrice($price->getPrice(), $state)
        );

        //check if a pseudo price is defined and calculates it too.
        if ($price->getPseudoPrice()) {
            $price->setCalculatedPseudoPrice(
                $this->calculatePrice($price->getPseudoPrice(), $state)
            );
        }

        //check if the product has unit definitions and calculate the reference price for the unit.
        if ($price->getUnit() && $price->getUnit()->getPurchaseUnit()) {
            $price->setCalculatedReferencePrice(
                $this->calculateReferencePrice($price)
            );
        }
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
     * @param Struct\GlobalState $state
     * @return float
     */
    private function calculatePrice($price, Struct\GlobalState $state)
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
        $customerGroup = $state->getCurrentCustomerGroup();

        /**
         * Basket discount calculation:
         *
         * Check if a global basket discount is configured and reduce the price
         * by the percentage discount value of the current customer group.
         */
        if ($customerGroup->getUseDiscount()
            && $customerGroup->getPercentageDiscount()) {

            $price = $price - ($price / 100 * $customerGroup->getPercentageDiscount());
        }

        /**
         * Currency calculation:
         * If the customer is currently in a sub shop with another currency, like dollar,
         * we have to calculate the the price for the other currency.
         */
        $price = $price * $state->getCurrency()->getFactor();


        //check if the customer group should see gross prices.
        if (!$customerGroup->displayGrossPrices()) {
            return $price;
        }

        /**
         * Gross calculation:
         *
         * This line contains the gross price calculation within the store front.
         *
         * The passed $state object contains a calculated Struct\Tax object which
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
        $price = $price * (100 + $state->getTax()->getTax()) / 100;

        return $price;
    }

    /**
     * Calculates the product unit reference price for the passed
     * product price.
     *
     * @param Struct\Price $price
     * @return float
     */
    private function calculateReferencePrice(Struct\Price $price)
    {
        return $price->getCalculatedPrice() / $price->getUnit()->getPurchaseUnit() * $price->getUnit()->getReferenceUnit();
    }
}