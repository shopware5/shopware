<?php

namespace Shopware\Struct\Property;

use Shopware\Struct\Extendable;

/**
 * @package Shopware\Struct
 */
class Set extends Extendable
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
     * @var Group[]
     */
    private $groups = array();

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
     * @param \Shopware\Struct\Property\Group[] $groups
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @return \Shopware\Struct\Property\Group[]
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