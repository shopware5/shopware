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

use Shopware\Bundle\CartBundle\Domain\Cart\CartContextInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Deliverable;
use Shopware\Bundle\CartBundle\Domain\LineItem\Stackable;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCalculator;
use Shopware\Bundle\CartBundle\Domain\Customer\Address;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;

class StockDeliverySeparator
{
    /**
     * @var PriceCalculator
     */
    private $priceCalculator;

    /**
     * @param PriceCalculator $priceCalculator
     */
    public function __construct(PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * @param DeliveryCollection $deliveries
     * @param CalculatedLineItemCollection $items
     * @param CartContextInterface $context
     * @return DeliveryCollection
     */
    public function addItemsToDeliveries(
        DeliveryCollection $deliveries,
        CalculatedLineItemCollection $items,
        CartContextInterface $context
    ) {
        foreach ($items as $item) {
            if (!$item instanceof Deliverable) {
                continue;
            }

            if ($deliveries->contains($item)) {
                continue;
            }

            $quantity = 1;
            if ($item instanceof Stackable) {
                $quantity = $item->getQuantity();
            }

            $position = new DeliveryPosition(
                $item->getIdentifier(),
                clone $item,
                $quantity,
                $item->getPrice(),
                $item->getInStockDeliveryDate()
            );

            //completly in stock?
            if ($item->getStock() >= $quantity) {
                $this->addGoodsToDelivery(
                    $deliveries,
                    $position,
                    $context->getShippingAddress(),
                    $context->getDeliveryService()
                );
                continue;
            }

            //completely out of stock? add full quantity to a delivery with same of out stock delivery date
            if ($item->getStock() <= 0) {
                $position = new DeliveryPosition(
                    $item->getIdentifier(),
                    clone $item,
                    $quantity,
                    $item->getPrice(),
                    $item->getOutOfStockDeliveryDate()
                );

                $this->addGoodsToDelivery(
                    $deliveries,
                    $position,
                    $context->getShippingAddress(),
                    $context->getDeliveryService()
                );
                continue;
            }

            $outOfStock = abs($item->getStock() - $quantity);

            $position = $this->recalculatePosition(
                $item,
                $item->getStock(),
                $item->getInStockDeliveryDate(),
                $context
            );

            $this->addGoodsToDelivery(
                $deliveries,
                $position,
                $context->getShippingAddress(),
                $context->getDeliveryService()
            );

            $position = $this->recalculatePosition(
                $item,
                $outOfStock,
                $item->getOutOfStockDeliveryDate(),
                $context
            );

            $this->addGoodsToDelivery(
                $deliveries,
                $position,
                $context->getShippingAddress(),
                $context->getDeliveryService()
            );
        }

        return clone $deliveries;
    }

    /**
     * @param Stackable|CalculatedLineItemInterface|Deliverable $item
     * @param float $quantity
     * @param DeliveryDate $deliveryDate
     * @param CartContextInterface $context
     * @return DeliveryPosition
     */
    private function recalculatePosition(
        Stackable $item,
        $quantity,
        DeliveryDate $deliveryDate,
        CartContextInterface $context
    ) {
        $definition = new PriceDefinition(
            $item->getPrice()->getUnitPrice(),
            $item->getPrice()->getTaxRules(),
            $quantity,
            true
        );

        $price = $this->priceCalculator->calculate($definition, $context);

        return new DeliveryPosition(
            $item->getIdentifier(),
            clone $item,
            $quantity,
            $price,
            $deliveryDate
        );
    }

    /**
     * @param DeliveryCollection $deliveries
     * @param DeliveryPosition $position
     * @param Address $address
     * @param DeliveryService $deliveryService
     */
    private function addGoodsToDelivery(
        DeliveryCollection $deliveries,
        DeliveryPosition $position,
        Address $address,
        DeliveryService $deliveryService
    ) {
        $delivery = $deliveries->getDelivery(
            $position->getDeliveryDate(),
            $address
        );

        if ($delivery) {
            $delivery->getPositions()->add($position);
            return;
        }

        $deliveries->add(
            new Delivery(
                new DeliveryPositionCollection([$position]),
                $position->getDeliveryDate(),
                $deliveryService,
                $address
            )
        );
    }
}
