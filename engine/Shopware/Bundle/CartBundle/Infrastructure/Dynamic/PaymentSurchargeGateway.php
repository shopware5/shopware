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
use Shopware\Bundle\CartBundle\Domain\Price\PercentagePriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Tax\PercentageTaxRuleBuilder;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class PaymentSurchargeGateway
{
    /**
     * @var PercentageTaxRuleBuilder
     */
    private $percentageTaxRuleBuilder;

    /**
     * @var PercentagePriceCalculator
     */
    private $percentagePriceCalculator;

    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    public function __construct(
        PercentageTaxRuleBuilder $percentageTaxRuleBuilder,
        PercentagePriceCalculator $percentagePriceCalculator,
        PriceCalculator $priceCalculator
    ) {
        $this->percentageTaxRuleBuilder = $percentageTaxRuleBuilder;
        $this->percentagePriceCalculator = $percentagePriceCalculator;
        $this->priceCalculator = $priceCalculator;
    }

    public function get(CalculatedCart $cart, ShopContextInterface $context): ? CalculatedLineItemInterface
    {
        if (!$context->getCustomer()) {
            return null;
        }

        $payment = $context->getPaymentMethod();

        $goods = $cart->getCalculatedLineItems()->filterGoods();

        switch (true) {
            case $payment->getSurcharge() !== null:
                $rules = $this->percentageTaxRuleBuilder->buildRules(
                    $goods->getPrices()->getTotalPrice()
                );
                $surcharge = $this->priceCalculator->calculate(
                    new PriceDefinition($payment->getSurcharge(), $rules, 1, true),
                    $context
                );

                break;

            case $payment->getPercentageSurcharge() !== null:
                $surcharge = $this->percentagePriceCalculator->calculate(
                    $payment->getPercentageSurcharge(),
                    $goods->getPrices(),
                    $context
                );

                break;
            default:
                return null;
        }

        return new Discount('payment', $surcharge, 'Payment surcharge');
    }
}
