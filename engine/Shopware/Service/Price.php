<?php

namespace Shopware\Service;

use Shopware\Gateway\Exception\NoCustomerGroupPriceFoundException;
use Shopware\Struct as Struct;
use Shopware\Gateway\ORM as Gateway;

class Price
{
    /**
     * @var \Shopware\Gateway\ORM\Price
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
     * Returns the scaled prices of the passed product for the current global state.
     * If the customer group of the current user has no own defined prices,
     * the function returns the scaled prices of the fallback customer group.
     *
     * @param Struct\ProductMini $product
     * @param Struct\GlobalState $state
     * @return Struct\Price[]
     */
    public function getProductPrices(Struct\ProductMini $product, Struct\GlobalState $state)
    {
        $prices = $this->priceGateway->getProductPrices(
            $product,
            $state->getCurrentCustomerGroup()
        );

        if (empty($prices)) {
            $prices = $this->priceGateway->getProductPrices(
                $product,
                $state->getFallbackCustomerGroup()
            );
        }

        foreach($prices as $price) {
            $price->setUnit($product->getUnit());
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
     * Returns the cheapest customer group price for a product.
     *
     * The function selects first the cheapest product price of the scaled prices.
     *
     * @param Struct\ProductMini $product
     * @param Struct\GlobalState $state
     * @return Struct\Price
     */
    public function getCheapestProductPrice(Struct\ProductMini $product, Struct\GlobalState $state)
    {
        $cheapestPrice = $this->priceGateway->getCheapestProductPrice(
            $product,
            $state->getCurrentCustomerGroup()
        );

        if ($cheapestPrice == null) {
            $cheapestPrice = $this->priceGateway->getCheapestProductPrice(
                $product,
                $state->getFallbackCustomerGroup()
            );
        }

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
        $customerGroup = $state->getCurrentCustomerGroup();

        if ($customerGroup->getUseDiscount()
            && $customerGroup->getPercentageDiscount()) {

            $price = $price - ($price / 100 * $customerGroup->getPercentageDiscount());
        }

        $price = $price * $state->getCurrency()->getFactor();

        //check if the customer group should see gross prices.
        if (!$customerGroup->displayGrossPrices()) {
            return $price;
        }

        //example:  20,- €   *   119 (tax)   /   100
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