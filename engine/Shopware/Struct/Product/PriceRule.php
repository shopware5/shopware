<?php

namespace Shopware\Struct\Product;

use Shopware\Struct\Customer\Group;
use Shopware\Struct\Extendable;

/**
 * @package Shopware\Struct
 */
class PriceRule extends Extendable
{
    /**
     * @var int
     */
    private $id;

    /**
     * Price value of the product price struct.
     *
     * @var float
     */
    private $price;

    /**
     * @var int
     */
    private $from;

    /**
     * @var null|int
     */
    private $to = null;

    /**
     * The pseudo price is used to fake a discount in the store front
     * without defining a global discount for a customer group.
     *
     * @var float
     */
    private $pseudoPrice;

    /**
     * Contains the associated customer group of this price.
     * Each graduated product price is defined for a single customer group.
     *
     * @var Group
     */
    private $customerGroup;

    /**
     * @var Unit
     */
    private $unit;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param int|null $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return int|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $pseudoPrice
     */
    public function setPseudoPrice($pseudoPrice)
    {
        $this->pseudoPrice = $pseudoPrice;
    }

    /**
     * @return float
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * @param \Shopware\Struct\Customer\Group $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return \Shopware\Struct\Customer\Group
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param \Shopware\Struct\Product\Unit $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return \Shopware\Struct\Product\Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }
}