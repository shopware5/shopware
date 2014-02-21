<?php

namespace Shopware\Components\Form\Interfaces;

use Shopware\Components\Form\Base;

interface Container
{
    /**
     * Overrides all elements of this container
     * @param $elements
     */
    public function setElements($elements);

    /**
     * Returns all elements of this container.
     * @return array
     */
    public function getElements();

    /**
     * Adds a new element to this container
     * @param Base $element
     */
    public function addElement(Base $element);

}