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

namespace Shopware\Bundle\CartBundle\Domain\Product;

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryInformation;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Deliverable;
use Shopware\Bundle\CartBundle\Domain\LineItem\Goods;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Stackable;
use Shopware\Bundle\CartBundle\Domain\Price\Price;

class CalculatedProduct implements CalculatedLineItemInterface, Stackable, Deliverable, Goods
{
    /**
     * @var LineItemInterface
     */
    protected $lineItem;

    /**
     * @var Price
     */
    protected $price;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var DeliveryInformation
     */
    protected $deliveryInformation;

    /**
     * @param string $identifier
     * @param float $quantity
     * @param LineItemInterface $lineItem
     * @param Price $price
     * @param DeliveryInformation $deliveryInformation
     * @internal param \DateTime $earliestDeliveryDate
     * @internal param \DateTime $latestDeliveryDate
     */
    public function __construct(
        $identifier,
        $quantity,
        LineItemInterface $lineItem,
        Price $price,
        DeliveryInformation $deliveryInformation
    ) {
        $this->identifier = $identifier;
        $this->quantity = $quantity;
        $this->lineItem = $lineItem;
        $this->price = $price;
        $this->deliveryInformation = $deliveryInformation;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
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
     * @return float
     */
    public function getStock()
    {
        return $this->deliveryInformation->getStock();
    }

    /**
     * @return DeliveryDate
     */
    public function getInStockDeliveryDate()
    {
        return $this->deliveryInformation->getInStockDeliveryDate();
    }

    /**
     * @return DeliveryDate
     */
    public function getOutOfStockDeliveryDate()
    {
        return $this->deliveryInformation->getOutOfStockDeliveryDate();
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return LineItemInterface
     */
    public function getLineItem()
    {
        return $this->lineItem;
    }
}
