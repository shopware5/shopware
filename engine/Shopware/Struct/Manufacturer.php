<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Manufacturer
{
    private $id;

    private $name;

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


}