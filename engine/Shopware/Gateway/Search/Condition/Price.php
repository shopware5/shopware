<?php

namespace Shopware\Gateway\Search\Condition;

class Price
{
    public $min;

    public $max;

    function __construct($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
    }
}