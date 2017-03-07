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

namespace Shopware\Bundle\CartBundle\Domain\Delivery;

use Shopware\Bundle\CartBundle\Domain\Cart\Cart;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContextInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\CartProcessorInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\LineItem\Deliverable;

class DeliverySeparatorProcessor implements CartProcessorInterface
{
    /**
     * @var StockDeliverySeparator
     */
    private $stockDeliverySeparator;

    /**
     * @param StockDeliverySeparator $stockDeliverySeparator
     */
    public function __construct(StockDeliverySeparator $stockDeliverySeparator)
    {
        $this->stockDeliverySeparator = $stockDeliverySeparator;
    }

    /**
     * @param Cart                 $cart
     * @param ProcessorCart        $processorCart
     * @param CartContextInterface $context
     */
    public function process(
        Cart $cart,
        ProcessorCart $processorCart,
        CartContextInterface $context
    ) {
        if (!$context->getShippingAddress()) {
            return;
        }

        $items = $processorCart
            ->getLineItems()
            ->filterClass(Deliverable::class);

        if (0 === count($items)) {
            return;
        }

        $deliveries = $this->stockDeliverySeparator->addItemsToDeliveries(
            $processorCart->getDeliveries(),
            $items,
            $context
        );

        $deliveries->sort();

        $processorCart->getDeliveries()->clear();
        $processorCart->getDeliveries()->fill($deliveries->getIterator()->getArrayCopy());
    }
}
