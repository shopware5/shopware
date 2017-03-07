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

namespace Shopware\Bundle\CartBundle\Domain\Price;

use Shopware\Bundle\CartBundle\Domain\Cart\CartContextInterface;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTax;
use Shopware\Bundle\CartBundle\Domain\Tax\PercentageTaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;

class PercentagePriceCalculator
{
    /**
     * @var PriceRounding
     */
    private $rounding;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @param PriceRounding   $rounding
     * @param PriceCalculator $priceCalculator
     */
    public function __construct(
        PriceRounding $rounding,
        PriceCalculator $priceCalculator
    ) {
        $this->rounding = $rounding;
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * Provide a negative percentage value for discount or a positive percentage value for a surcharge
     *
     * @param float                $percentage 10.00 for 10%, -10.0 for -10%
     * @param PriceCollection      $prices
     * @param CartContextInterface $context
     *
     * @return Price
     */
    public function calculatePrice(
        $percentage,
        PriceCollection $prices,
        CartContextInterface $context
    ) {
        $price = $prices->getTotalPrice();

        $discount = $this->rounding->round($price->getPrice() / 100 * $percentage);

        $rules = $this->buildPercentageTaxRule($price);

        $definition = new PriceDefinition($discount, $rules, 1, true);

        return $this->priceCalculator->calculate($definition, $context);
    }

    /**
     * @param Price $price
     *
     * @return TaxRuleCollection
     */
    private function buildPercentageTaxRule(Price $price)
    {
        $rules = new TaxRuleCollection();

        /** @var CalculatedTax $tax */
        foreach ($price->getCalculatedTaxes() as $tax) {
            $rules->add(
                new PercentageTaxRule(
                    $tax->getTaxRate(),
                    $tax->getPrice() / $price->getPrice() * 100
                )
            );
        }

        return $rules;
    }
}
