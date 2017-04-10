<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Gateway\PriceGroupDiscountGateway;
use Shopware\Bundle\StoreFrontBundle\Service\PriceCalculationServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PriceCalculationService implements PriceCalculationServiceInterface
{
    /**
     * @var PriceGroupDiscountGateway
     */
    private $priceGroupDiscountGateway;

    /**
     * @param PriceGroupDiscountGateway $priceGroupDiscountGateway
     */
    public function __construct(PriceGroupDiscountGateway $priceGroupDiscountGateway)
    {
        $this->priceGroupDiscountGateway = $priceGroupDiscountGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateProduct(
        ListProduct $product,
        ShopContextInterface $context
    ) {
        $tax = $context->getTaxRule($product->getTax()->getId());

        $prices = [];
        foreach ($product->getPriceRules() as $rule) {
            $prices[] = $this->calculatePriceStruct($rule, $tax, $context);
        }

        $product->setPrices($prices);

        $rules = $product->getPriceRules();
        if (!$product->getCheapestPriceRule() && !empty($rules)) {
            $product->setCheapestPriceRule(clone $rules[0]);
        }

        if ($product->getCheapestPriceRule()) {
            /**
             * Calculation with considering min purchase
             */
            $rule = clone $product->getCheapestPriceRule();
            $product->setCheapestPrice(
                $this->calculateCheapestAvailablePrice($product, $rule, $context)
            );

            /**
             * Calculation without considering min purchase
             */
            $rule = clone $product->getCheapestPriceRule();
            $product->setCheapestUnitPrice(
                $this->calculatePriceStruct($rule, $tax, $context)
            );
        }

        //add state to the product which can be used to check if the prices are already calculated.
        $product->addState(ListProduct::STATE_PRICE_CALCULATED);
    }

    /**
     * Calculates the cheapest price considering the variant min purchase
     *
     * @param ListProduct          $product
     * @param PriceRule            $priceRule
     * @param ShopContextInterface $context
     *
     * @return Price
     */
    private function calculateCheapestAvailablePrice(
        ListProduct $product,
        PriceRule $priceRule,
        ShopContextInterface $context
    ) {
        $priceRule->setPrice(
            $priceRule->getUnit()->getMinPurchase() * $priceRule->getPrice()
        );
        $priceRule->getUnit()->setPurchaseUnit(
            $priceRule->getUnit()->getMinPurchase() * $priceRule->getUnit()->getPurchaseUnit()
        );
        $priceRule->setPseudoPrice(
            $priceRule->getUnit()->getMinPurchase() * $priceRule->getPseudoPrice()
        );
        $tax = $context->getTaxRule($product->getTax()->getId());

        return $this->calculatePriceStruct($priceRule, $tax, $context);
    }

    /**
     * Helper function which calculates a single price struct of a product.
     * The product can contains multiple price struct elements like the graduated prices
     * and the cheapest price struct.
     * All price structs will be calculated through this function.
     *
     * @param PriceRule            $rule
     * @param Tax                  $tax
     * @param ShopContextInterface $context
     *
     * @return Price
     */
    private function calculatePriceStruct(
        PriceRule $rule,
        Tax $tax,
        ShopContextInterface $context
    ) {
        $price = new Price($rule);

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
     * @param Tax                  $tax
     * @param ShopContextInterface $context
     *
     * @return float
     */
    private function calculatePrice($price, Tax $tax, ShopContextInterface $context)
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

        /*
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

        /*
         * check if the customer group should see gross prices.
         */
        if (!$customerGroup->displayGrossPrices()) {
            return round($price, 3);
        }

        /**
         * Gross calculation:
         *
         * This line contains the gross price calculation within the store front.
         *
         * The passed $context object contains a calculated Tax object which
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

        return round($price, 3);
    }

    /**
     * Calculates the product unit reference price for the passed
     * product price.
     *
     * @param Price $price
     *
     * @return float
     */
    private function calculateReferencePrice(Price $price)
    {
        $value = $price->getCalculatedPrice() / $price->getUnit()->getPurchaseUnit() * $price->getUnit()->getReferenceUnit();

        return round($value, 3);
    }
}
