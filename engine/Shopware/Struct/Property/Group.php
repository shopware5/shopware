<?php

namespace Shopware\Struct\Property;

/**
 * @package Shopware\Struct
 */
class Group
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var boolean
     */
    private $filterable;

    /**
     * @var Option[]
     */
    private $options;

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \Shopware\Struct\Property\Option[] $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return \Shopware\Struct\Property\Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param boolean $filterable
     */
    public function setFilterable($filterable)
    {
        $this->filterable = $filterable;
    }

    /**
     * @return boolean
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

}