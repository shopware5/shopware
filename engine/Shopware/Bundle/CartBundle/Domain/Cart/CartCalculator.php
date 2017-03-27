<?php
declare(strict_types=1);
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

namespace Shopware\Bundle\CartBundle\Domain\Cart;

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Price\AmountCalculator;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CartCalculator
{
    /**
     * @var CartProcessorInterface[]
     */
    private $processors = [];

    /**
     * @var AmountCalculator
     */
    private $amountCalculator;

    /**
     * @param CartProcessorInterface[] $processors
     * @param AmountCalculator         $amountCalculator
     */
    public function __construct(
        array $processors,
        AmountCalculator $amountCalculator
    ) {
        $this->processors = $processors;
        $this->amountCalculator = $amountCalculator;
    }

    public function calculate(CartContainer $cartContainer, ShopContextInterface $context): CalculatedCart
    {
        $processorCart = new ProcessorCart(
            new CalculatedLineItemCollection(),
            new DeliveryCollection()
        );

        foreach ($this->processors as $processor) {
            $processor->process(
                $cartContainer,
                $this->createCalculatedCart($cartContainer, $context, $processorCart),
                $processorCart,
                $context
            );
        }

        return $this->createCalculatedCart($cartContainer, $context, $processorCart);
    }

    private function createCalculatedCart(
        CartContainer $cartContainer,
        ShopContextInterface $context,
        ProcessorCart $processorCart
    ): CalculatedCart {
        return new CalculatedCart(
            $cartContainer,
            $processorCart->getLineItems(),
            $this->amountCalculator->calculateAmount(
                $processorCart->getLineItems()->getPrices(),
                $context
            ),
            $processorCart->getDeliveries(),
            $processorCart->getErrors()
        );
    }
}
