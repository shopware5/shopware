<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Tax
{
    /**
     * Unique identifier of the tax struct.
     * @var int
     */
    private $id;

    /**
     * Contains an alphanumeric tax name.
     *
     * @var string
     */
    private $name;

    /**
     * Contains the tax rate value.
     *
     * @var float
     */
    private $tax;

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
     * @param float $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }
}