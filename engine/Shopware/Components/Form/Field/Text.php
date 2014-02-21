<?php

namespace Shopware\Components\Form\Field;

use Shopware\Components\Form\Field;

class Text extends Field
{
    /**
     * Align of the field value.
     * @var string
     */
    protected $align = 'left';

    /**
     * Requires to set a name for the field
     * @param $name
     */
    function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $align
     */
    public function setAlign($align)
    {
        $this->align = $align;
    }

    /**
     * @return string
     */
    public function getAlign()
    {
        return $this->align;
    }
}