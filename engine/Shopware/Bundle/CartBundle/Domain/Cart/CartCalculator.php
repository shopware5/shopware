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
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class CartCalculator
{
    /**
     * @var CartProcessorInterface[]
     */
    private $processors;

    /**
     * @var CalculatedCartGenerator
     */
    private $calculatedCartGenerator;

    public function __construct(
        array $processors,
        CalculatedCartGenerator $calculatedCartGenerator
    ) {
        $this->processors = $processors;
        $this->calculatedCartGenerator = $calculatedCartGenerator;
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
                $processorCart,
                $context
            );
        }

        return $this->calculatedCartGenerator->create($cartContainer, $context, $processorCart);
    }
}
