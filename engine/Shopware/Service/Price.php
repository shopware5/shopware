<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;

class Price
{
    private $priceGateway;

    function __construct($priceGateway)
    {
        $this->priceGateway = $priceGateway;
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
            if ($product->getReferenceUnit() && $product->getPurchaseUnit()) {

                $price->setCalculatedReferencePrice(
                    $this->calculateReferencePrice($product, $price)
                );

            }
        }

        //add state to the product which can be used to check if the prices are already calculated.
        $product->addState(Struct\ProductMini::STATE_PRICE_CALCULATED);
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

        if ($customerGroup->useDicount()
            && $customerGroup->getPercentageDiscount()) {

            $price = $price - ($price / 100 * $customerGroup->getPercentageDiscount());
        }

        $price = $price * $state->getCurrency()->getFactor();

        //check if the customer group should see gross prices.
        if (!$customerGroup->displayGrossPrices()) {
            return $price;
        }

        //example:  20,- €   *   119(tax)   /   100
        $price = $price * (100 + $state->getTax()->getTax()) / 100;

        return $price;
    }

    /**
     * @param Struct\ProductMini $product
     * @param Struct\Price $price
     * @return float
     */
    private function calculateReferencePrice(Struct\ProductMini $product, Struct\Price $price)
    {
        return $price->getCalculatedPrice() / $product->getPurchaseUnit() * $product->getReferenceUnit();
    }
}