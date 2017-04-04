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

namespace Shopware\Bundle\CartBundle\Infrastructure\Payment;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentMethod;
use Shopware\Bundle\CartBundle\Domain\Validator\Collector\RuleDataCollectorRegistry;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class RiskManagementPaymentFilter
{
    /**
     * @var RuleDataCollectorRegistry
     */
    private $riskDataCollectorRegistry;

    public function __construct(RuleDataCollectorRegistry $riskDataCollectorRegistry)
    {
        $this->riskDataCollectorRegistry = $riskDataCollectorRegistry;
    }

    public function filter(array $payments, CalculatedCart $calculatedCart, ShopContextInterface $context): array
    {
        $rules = array_map(function (PaymentMethod $paymentMethod) {
            return $paymentMethod->getRiskManagementRule();
        }, $payments);

        $dataCollection = $this->riskDataCollectorRegistry->collect(
            $calculatedCart,
            $context,
            new RuleCollection(array_filter($rules))
        );

        return array_filter(
            $payments,
            function (PaymentMethod $method) use ($calculatedCart, $context, $dataCollection) {
                $rule = $method->getRiskManagementRule();
                if (!$rule) {
                    return true;
                }

                return !$rule->match($calculatedCart, $context, $dataCollection);
            }
        );
    }
}
