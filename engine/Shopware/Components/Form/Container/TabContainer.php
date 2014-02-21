<?php

namespace Shopware\Components\Form\Container;

use Shopware\Components\Form\Base;
use Shopware\Components\Form\Container;

class TabContainer extends Container
{
    /**
     * @var string $title
     */
    protected $title;

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param Base $element
     * @throws \InvalidArgumentException
     */
    public function addElement(Base $element)
    {
        if (!$element instanceof Tab) {
            throw new \InvalidArgumentException(
                '$element must be instance of Shopware\Components\Form\Container\Tab'
            );
        }
        $this->addTab($element);
    }

    /**
     * @param Tab $element
     */
    public function addTab(Tab $element)
    {
        parent::addElement($element);
    }
}