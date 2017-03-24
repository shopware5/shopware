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

namespace Shopware\Bundle\CartBundle\Domain\Payment;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\Cart;
use Shopware\Bundle\CartBundle\Domain\Cart\CartProcessorInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\Error\PaymentBlockedError;
use Shopware\Bundle\CartBundle\Domain\RiskManagement\Collector\RiskDataCollectorRegistry;
use Shopware\Bundle\CartBundle\Domain\RiskManagement\Rule\RuleCollection;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PaymentValidatorProcessor implements CartProcessorInterface
{
    /**
     * @var RiskDataCollectorRegistry
     */
    private $riskDataRegistry;

    public function __construct(RiskDataCollectorRegistry $riskDataRegistry)
    {
        $this->riskDataRegistry = $riskDataRegistry;
    }

    public function process(
        Cart $cart,
        CalculatedCart $calculatedCart,
        ProcessorCart $processorCart,
        ShopContextInterface $context
    ): void {
        if (!$context->getCustomer()) {
            return;
        }

        $payment = $context->getPaymentMethod();

        if (!$rule = $payment->getRiskManagementRule()) {
            return;
        }

        $data = $this->riskDataRegistry->collect($calculatedCart, $context, new RuleCollection([$rule]));

        if ($rule->validate($calculatedCart, $context, $data)) {
            return;
        }

        $processorCart->addError(
            new PaymentBlockedError($payment->getId(), $payment->getLabel())
        );
    }
}
