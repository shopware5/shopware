<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;

class Price extends Condition
{
    public $min;

    public $max;

    public $customerGroupKey;

    function __construct($min, $max, $customerGroupKey)
    {
        $this->min = $min;
        $this->max = $max;
        $this->customerGroupKey = $customerGroupKey;
    }

    public function getName()
    {
        return 'price';
    }
}