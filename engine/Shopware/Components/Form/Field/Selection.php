<?php

namespace Shopware\Components\Form\Field;

use Shopware\Components\Form\Field;

class Selection extends Field
{
    /**
     * Contains the store data for the selection field.
     *
     * @var mixed
     */
    protected $store;

    /**
     * Requires to set a name for the field
     * @param $name
     * @param $store
     */
    function __construct($name, $store)
    {
        $this->name = $name;
        $this->store = $store;
    }

    /**
     * @param mixed $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * @return array
     */
    public function getStore()
    {
        return $this->store;
    }

}