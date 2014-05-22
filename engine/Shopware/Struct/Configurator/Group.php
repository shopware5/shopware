<?php

namespace Shopware\Struct\Configurator;

use Shopware\Struct\Extendable;

class Group extends Extendable
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
    private $description;

    /**
     * @var Option[]
     */
    private $options = array();

    /**
     * @var bool
     */
    private $selected = false;

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
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \Shopware\Struct\Configurator\Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param \Shopware\Struct\Configurator\Option[] $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function addOption(Option $option)
    {
        $this->options[] = $option;
    }

    /**
     * @return boolean
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * @param boolean $selected
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;
    }
}