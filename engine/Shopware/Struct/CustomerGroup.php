<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class CustomerGroup extends Extendable
{
    /**
     * Unique identifier
     * @var int
     */
    private $id;

    /**
     * Alphanumeric unique identifier.
     *
     * @var string
     */
    private $key;

    /**
     * Name of the customer group
     * @var string
     */
    private $name;

    /**
     * Defines if the customer group
     * should see gross prices in the store
     * front.
     *
     * @var boolean
     */
    private $displayGrossPrices;

    /**
     * Defines if the display price
     * already reduces with a global customer
     * group discount
     *
     * @var boolean
     */
    private $useDiscount;

    /**
     * Percentage global discount value
     * for this customer group.
     * @var float
     */
    private $percentageDiscount;

    /**
     * Minimal order value for the customer group.
     * If this value isn't reached in the basket,
     * the defined surcharge will be added
     * as basket position.
     *
     * @var float
     */
    private $minimumOrderValue;

    /**
     * Numeric surcharge value for the customer group.
     * This value is used for the additional basket
     * position if the $minimumOrderValue of the
     * customer group isn't reached.
     *
     * @var float
     */
    private $surcharge;

    /**
     * @param mixed $id
     *
     */
    public function setId($id)
    {
        $this->id = $id;

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $key
     *
     */
    public function setKey($key)
    {
        $this->key = $key;

    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $name
     *
     */
    public function setName($name)
    {
        $this->name = $name;

    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $displayGrossPrices
     */
    public function setDisplayGross($displayGrossPrices)
    {
        $this->displayGrossPrices = $displayGrossPrices;
    }

    /**
     * @return mixed
     */
    public function displayGrossPrices()
    {
        return $this->displayGrossPrices;
    }

    /**
     * @param mixed $useDiscount
     *
     */
    public function setUseDiscount($useDiscount)
    {
        $this->useDiscount = $useDiscount;

    }

    /**
     * @return mixed
     */
    public function getUseDiscount()
    {
        return $this->useDiscount;
    }

    /**
     * @param mixed $percentageDiscount
     *
     */
    public function setPercentageDiscount($percentageDiscount)
    {
        $this->percentageDiscount = $percentageDiscount;

    }

    /**
     * @return mixed
     */
    public function getPercentageDiscount()
    {
        return $this->percentageDiscount;
    }

    /**
     * @param mixed $minimumOrderValue
     *
     */
    public function setMinimumOrderValue($minimumOrderValue)
    {
        $this->minimumOrderValue = $minimumOrderValue;

    }

    /**
     * @return mixed
     */
    public function getMinimumOrderValue()
    {
        return $this->minimumOrderValue;
    }

    /**
     * @param mixed $surcharge
     *
     */
    public function setSurcharge($surcharge)
    {
        $this->surcharge = $surcharge;

    }

    /**
     * @return mixed
     */
    public function getSurcharge()
    {
        return $this->surcharge;
    }

}