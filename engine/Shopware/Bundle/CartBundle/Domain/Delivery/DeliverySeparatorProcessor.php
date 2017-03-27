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

namespace Shopware\Bundle\CartBundle\Domain\Delivery;

use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Cart\CartProcessorInterface;
use Shopware\Bundle\CartBundle\Domain\Cart\ProcessorCart;
use Shopware\Bundle\CartBundle\Domain\LineItem\Deliverable;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class DeliverySeparatorProcessor implements CartProcessorInterface
{
    /**
     * @var StockDeliverySeparator
     */
    private $stockDeliverySeparator;

    public function __construct(StockDeliverySeparator $stockDeliverySeparator)
    {
        $this->stockDeliverySeparator = $stockDeliverySeparator;
    }

    public function process(
        CartContainer $cartContainer,
        ProcessorCart $processorCart,
        ShopContextInterface $context
    ): void {
        $items = $processorCart
            ->getLineItems()
            ->filterInstance(Deliverable::class);

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
