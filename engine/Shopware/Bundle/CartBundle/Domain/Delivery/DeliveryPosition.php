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

use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Deliverable;
use Shopware\Bundle\CartBundle\Domain\LineItem\Stackable;
use Shopware\Bundle\CartBundle\Domain\Price\Price;

class DeliveryPosition implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @var CalculatedLineItemInterface|Deliverable
     */
    protected $item;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var Price
     */
    protected $price;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var DeliveryDate
     */
    private $deliveryDate;

    /**
     * @param string                                  $identifier
     * @param CalculatedLineItemInterface|Deliverable $item
     * @param float                                   $quantity
     * @param Price                                   $price
     * @param DeliveryDate                            $deliveryDate
     */
    public function __construct(
        $identifier,
        CalculatedLineItemInterface $item,
        $quantity,
        Price $price,
        DeliveryDate $deliveryDate
    ) {
        $this->item = $item;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->identifier = $identifier;
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @param Deliverable|CalculatedLineItemInterface|Stackable $lineItem
     *
     * @return DeliveryPosition
     */
    public static function createByLineItemForInStockDate($lineItem)
    {
        return new self(
            $lineItem->getIdentifier(),
            clone $lineItem,
            $lineItem->getQuantity(),
            $lineItem->getPrice(),
            $lineItem->getInStockDeliveryDate()
        );
    }

    /**
     * @param Deliverable|CalculatedLineItemInterface|Stackable $lineItem
     *
     * @return DeliveryPosition
     */
    public static function createByLineItemForOutOfStockDate($lineItem)
    {
        return new self(
            $lineItem->getIdentifier(),
            clone $lineItem,
            $lineItem->getQuantity(),
            $lineItem->getPrice(),
            $lineItem->getOutOfStockDeliveryDate()
        );
    }

    /**
     * @return CalculatedLineItemInterface|Deliverable
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return DeliveryDate
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }
}
