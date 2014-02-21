<?php

namespace Shopware\Components\Form\Container;

use Shopware\Components\Form\Base;
use Shopware\Components\Form\Container;

class TabContainer extends Container
{
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