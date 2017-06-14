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

namespace Shopware\Bundle\CartBundle\Infrastructure\Dynamic;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Discount;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Tax\PercentageTaxRuleBuilder;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class MinimumOrderValueGateway
{
    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var PercentageTaxRuleBuilder
     */
    private $percentageTaxRuleBuilder;

    /**
     * @param PriceCalculator          $priceCalculator
     * @param PercentageTaxRuleBuilder $percentageTaxRuleBuilder
     */
    public function __construct(PriceCalculator $priceCalculator, PercentageTaxRuleBuilder $percentageTaxRuleBuilder)
    {
        $this->priceCalculator = $priceCalculator;
        $this->percentageTaxRuleBuilder = $percentageTaxRuleBuilder;
    }

    public function get(CalculatedCart $cart, ShopContextInterface $context): ? CalculatedLineItemInterface
    {
        if (!$context->getCustomer()) {
            return null;
        }

        $customerGroup = $context->getCurrentCustomerGroup();

        if (!$customerGroup->getMinimumOrderValue()) {
            return null;
        }

        $goods = $cart->getCalculatedLineItems()->filterGoods();

        if (0 === $goods->count()) {
            return null;
        }

        $price = $goods->getPrices()->sum();

        if ($customerGroup->getMinimumOrderValue() <= $price->getTotalPrice()) {
            return null;
        }

        $rules = $this->percentageTaxRuleBuilder->buildRules($price);

        $surcharge = $this->priceCalculator->calculate(
            new PriceDefinition($customerGroup->getSurcharge(), $rules, 1, true),
            $context
        );

        return new Discount(
            'minimum-order-value',
            $surcharge,
            sprintf('Minimum order value of %s', $customerGroup->getMinimumOrderValue())
        );
    }
}
