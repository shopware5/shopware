<?php

namespace Shopware\Components\Form\Container;

use Shopware\Components\Form\Container as BaseContainer;

class FieldSet extends BaseContainer
{
    /**
     * @var string $label
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

}