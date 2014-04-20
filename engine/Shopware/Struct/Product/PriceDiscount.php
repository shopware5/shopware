<?php

namespace Shopware\Struct\Product;

use Shopware\Struct\Customer\Group;

class PriceDiscount
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Group
     */
    private $customerGroup;

    /**
     * @var PriceGroup
     */
    private $priceGroup;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $percent;

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
     * @param \Shopware\Struct\Product\PriceGroup $priceGroup
     */
    public function setPriceGroup($priceGroup)
    {
        $this->priceGroup = $priceGroup;
    }

    /**
     * @return \Shopware\Struct\Product\PriceGroup
     */
    public function getPriceGroup()
    {
        return $this->priceGroup;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $percent
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;
    }

    /**
     * @return float
     */
    public function getPercent()
    {
        return $this->percent;
    }


}