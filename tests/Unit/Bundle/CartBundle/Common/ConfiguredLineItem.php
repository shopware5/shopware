<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Common;

use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryInformation;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Deliverable;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Stackable;
use Shopware\Bundle\CartBundle\Domain\Price\Price;

class ConfiguredLineItem implements Deliverable, CalculatedLineItemInterface, Stackable
{
    use JsonSerializableTrait;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var Price
     */
    private $price;

    /**
     * @var LineItemInterface
     */
    private $lineItem;

    /**
     * @var DeliveryInformation
     */
    private $deliveryInformation;

    /**
     * @param string $identifier
     * @param float $quantity
     * @param Price $price
     * @param LineItemInterface $lineItem
     * @param DeliveryInformation $deliveryInformation
     */
    public function __construct(
        $identifier,
        $quantity = null,
        Price $price = null,
        LineItemInterface $lineItem = null,
        DeliveryInformation $deliveryInformation = null
    ) {
        $this->identifier = $identifier;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->lineItem = $lineItem;
        if ($deliveryInformation === null) {
            $deliveryInformation = new DeliveryInformation(
                0,
                0,
                0,
                0,
                0,
                new DeliveryDate(new \DateTime(), new \DateTime()),
                new DeliveryDate(new \DateTime(), new \DateTime())
            );
        }
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
     * @return LineItemInterface
     */
    public function getLineItem()
    {
        return $this->lineItem;
    }

    /**
     * @return float
     */
    public function getStock()
    {
        return $this->deliveryInformation->getStock();
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
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
     * @return DeliveryInformation
     */
    public function getDeliveryInformation()
    {
        return $this->deliveryInformation;
    }
}
