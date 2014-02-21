<?php

namespace Shopware\Components\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form\Interfaces\Container as ContainerInterface;

class Container extends Base implements ContainerInterface
{
    /**
     * @var ArrayCollection
     */
    protected $elements;

    /**
     * Initials the elements collection.
     */
    function __construct()
    {
        $this->elements = new ArrayCollection();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param $element
     */
    public function addElement(Base $element)
    {
        $this->elements->add($element);
    }
}