<?php

namespace Shopware\Gateway\Search\Sorting;

use Shopware\Gateway\Search\Sorting;

class Price extends Sorting
{
    public $customerGroupKey;

    function __construct($direction = 'ASC', $customerGroupKey)
    {
        $this->direction = $direction;
        $this->customerGroupKey = $customerGroupKey;
    }

}