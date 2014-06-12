<?php

namespace Shopware\Struct\Configurator;

use Shopware\Struct\Extendable;

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
     * @var string
     */
    private $type;

    /**
     * @var Group[]
     */
    private $groups = array();

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
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \Shopware\Struct\Configurator\Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param \Shopware\Struct\Configurator\Group[] $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }
}
