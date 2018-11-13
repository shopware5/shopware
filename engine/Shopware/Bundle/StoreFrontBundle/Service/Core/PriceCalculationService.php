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

use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PriceCalculationService implements Service\PriceCalculationServiceInterface
{
    /**
     * @var Service\PriceCalculatorInterface
     */
    private $priceCalculatorService;

    /**
     * @param Service\PriceCalculatorInterface $priceCalculatorService
     */
    public function __construct(Service\PriceCalculatorInterface $priceCalculatorService)
    {
        $this->priceCalculatorService = $priceCalculatorService;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateProduct(
        Struct\ListProduct $product,
        Struct\ProductContextInterface $context
    ) {
        $tax = $context->getTaxRule($product->getTax()->getId());

        $rules = $product->getPriceRules();

        $prices = [];
        foreach ($rules as $rule) {
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

        // Add state to the product which can be used to check if the prices are already calculated.
        $product->addState(Struct\ListProduct::STATE_PRICE_CALCULATED);
    }

    /**
     * Calculates the cheapest price considering the variant min purchase
     *
     * @param Struct\ListProduct             $product
     * @param Struct\Product\PriceRule       $priceRule
     * @param Struct\ProductContextInterface $context
     *
     * @return Struct\Product\Price
     */
    private function calculateCheapestAvailablePrice(
        Struct\ListProduct $product,
        Struct\Product\PriceRule $priceRule,
        Struct\ProductContextInterface $context
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
     * @param Struct\Product\PriceRule       $rule
     * @param Struct\Tax                     $tax
     * @param Struct\ProductContextInterface $context
     *
     * @return Struct\Product\Price
     */
    private function calculatePriceStruct(
        Struct\Product\PriceRule $rule,
        Struct\Tax $tax,
        Struct\ProductContextInterface $context
    ) {
        $price = new Struct\Product\Price($rule);

        // Calculates the normal price of the struct.
        $price->setCalculatedPrice(
            $this->priceCalculatorService->calculatePrice($rule->getPrice(), $tax, $context)
        );

        // Check if a pseudo price is defined and calculates it too.
        $price->setCalculatedPseudoPrice(
            $this->priceCalculatorService->calculatePrice($rule->getPseudoPrice(), $tax, $context)
        );

        // Check if the product has unit definitions and calculate the reference price for the unit.
        if ($price->getUnit() && $price->getUnit()->getPurchaseUnit()) {
            $price->setCalculatedReferencePrice(
                $this->calculateReferencePrice($price)
            );
        }

        return $price;
    }

    /**
     * Calculates the product unit reference price for the passed
     * product price.
     *
     * @param Struct\Product\Price $price
     *
     * @return float
     */
    private function calculateReferencePrice(Struct\Product\Price $price)
    {
        $value = $price->getCalculatedPrice() / $price->getUnit()->getPurchaseUnit() * $price->getUnit()->getReferenceUnit();

        return round($value, 2);
    }
}
