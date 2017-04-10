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

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCartGenerator;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\CartProcessorInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\Error\PaymentBlockedError;
use Shopware\Bundle\CartBundle\Domain\Validator\ValidatableFilter;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class PaymentValidatorProcessor implements CartProcessorInterface
{
    /**
     * @var CalculatedCartGenerator
     */
    private $calculatedCartGenerator;

    /**
     * @var ValidatableFilter
     */
    private $validatableFilter;

    public function __construct(
        ValidatableFilter $validatableFilter,
        CalculatedCartGenerator $calculatedCartGenerator
    ) {
        $this->calculatedCartGenerator = $calculatedCartGenerator;
        $this->validatableFilter = $validatableFilter;
    }

    public function process(
        CartContainer $cartContainer,
        ProcessorCart $processorCart,
        ShopContextInterface $context
    ): void {
        if (!$context->getCustomer()) {
            return;
        }

        $payment = $context->getPaymentMethod();

        if (!$payment->getRule()) {
            return;
        }

        $calculatedCart = $this->calculatedCartGenerator->create($cartContainer, $context, $processorCart);

        $valid = $this->validatableFilter->filter([$payment], $calculatedCart, $context);

        if (!empty($valid)) {
            return;
        }

        $processorCart->getErrors()->add(
            new PaymentBlockedError($payment->getId(), $payment->getLabel())
        );
    }
}
