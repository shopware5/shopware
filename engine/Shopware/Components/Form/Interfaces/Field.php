<?php

namespace Shopware\Components\Form\Interfaces;

interface Field
{
    /**
     * Sets the name of this field.
     * @param string $name
     */
    public function setName($name);

    /**
     * Returns the name of this field.
     * @return string
     */
    public function getName();

}