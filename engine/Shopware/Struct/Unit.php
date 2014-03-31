<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Unit
{
    private $id;

    private $name;

    private $unit;

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
     * @param mixed $unit
     *
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }


}