<?php

namespace Shopware\Components\Form\Field;

use Shopware\Components\Form\Field;

class Number extends Field
{
    /**
     * @var int
     */
    protected $precision = 2;

    /**
     * Requires to set a name for the field
     * @param $name
     */
    function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param int $precision
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    /**
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }
}