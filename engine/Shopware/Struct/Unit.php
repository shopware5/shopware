<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Unit extends Base
{
    /**
     * Unique identifier of the struct.
     *
     * @var int
     */
    private $id;

    /**
     * Contains a name of the unit.
     * This value will be translated over the translation service.
     *
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $unit;

    /**
     * Contains the numeric value of the purchase unit.
     * Used to calculate the unit price of the product.
     *
     * Example:
     *  reference unit equals 1.0 liter
     *  purchase unit  equals 0.7 liter
     *
     *  product price       7,- €
     *  reference price    10,- €
     *
     * @var float
     */
    private $purchaseUnit;

    /**
     * Contains the numeric value of the reference unit.
     * Used to calculate the unit price of the product.
     *
     * Example:
     *  reference unit equals 1.0 liter
     *  purchase unit  equals 0.7 liter
     *  product price       7,- €
     *  reference price    10,- €
     *
     * @var float
     */
    private $referenceUnit;

    /**
     * Alphanumeric description how the product
     * units are delivered.
     *
     * Example: bottle, box, pair
     *
     * @var string
     */
    private $packUnit;

    /**
     * @param int $id
     *
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $unit
     *
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }


    /**
     * @return string
     */
    public function getPackUnit()
    {
        return $this->packUnit;
    }

    /**
     * @param string $packUnit
     */
    public function setPackUnit($packUnit)
    {
        $this->packUnit = $packUnit;

    }

    /**
     * @return float
     */
    public function getPurchaseUnit()
    {
        return $this->purchaseUnit;
    }

    /**
     * @param float $purchaseUnit
     */
    public function setPurchaseUnit($purchaseUnit)
    {
        $this->purchaseUnit = $purchaseUnit;
    }

    /**
     * @return float
     */
    public function getReferenceUnit()
    {
        return $this->referenceUnit;
    }

    /**
     * @param float $referenceUnit
     */
    public function setReferenceUnit($referenceUnit)
    {
        $this->referenceUnit = $referenceUnit;
    }
}