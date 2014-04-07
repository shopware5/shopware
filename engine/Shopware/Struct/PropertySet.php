<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class PropertySet
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
    private $comparable;

    /**
     * @var PropertyGroup[]
     */
    private $groups;

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
     * @param \Shopware\Struct\PropertyGroup[] $groups
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @return \Shopware\Struct\PropertyGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param boolean $comparable
     * @return $this
     */
    public function setComparable($comparable)
    {
        $this->comparable = $comparable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isComparable()
    {
        return $this->comparable;
    }
}