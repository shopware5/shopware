<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Tax
{
    private $id;

    private $name;

    private $tax;

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
     * @param float $tax
     *
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