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

namespace Shopware\Bundle\CartBundle\Domain\Product;

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryInformation;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Deliverable;
use Shopware\Bundle\CartBundle\Domain\LineItem\Goods;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\StoreFrontBundle\Common\Struct;

class CalculatedProduct extends Struct implements CalculatedLineItemInterface, Deliverable, Goods
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
     * @var int
     */
    protected $quantity;

    /**
     * @var DeliveryInformation
     */
    protected $deliveryInformation;

    public function __construct(
        string $identifier,
        int $quantity,
        LineItemInterface $lineItem,
        Price $price,
        DeliveryInformation $deliveryInformation
    ) {
        $this->identifier = $identifier;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->lineItem = $lineItem;
        $this->deliveryInformation = $deliveryInformation;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->deliveryInformation->getStock();
    }

    public function getInStockDeliveryDate(): DeliveryDate
    {
        return $this->deliveryInformation->getInStockDeliveryDate();
    }

    public function getOutOfStockDeliveryDate(): DeliveryDate
    {
        return $this->deliveryInformation->getOutOfStockDeliveryDate();
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getLineItem(): LineItemInterface
    {
        return $this->lineItem;
    }
}
