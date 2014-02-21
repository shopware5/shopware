<?php

namespace Shopware\Components\Form\Field;

use Shopware\Components\Form\Field;

class Date extends Field
{

    /**
     * Format of the date value.
     * @var string
     */
    private $format;

    /**
     * Requires to set a name for the field
     * @param $name
     */
    function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
}