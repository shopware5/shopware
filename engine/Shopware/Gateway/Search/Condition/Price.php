<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;
use Shopware\Struct\Customer\Group;

class Price extends Condition
{
    public $min;

    public $max;

    /**
     * @var \Shopware\Struct\Customer\Group
     */
    public $currentCustomerGroup;

    /**
     * @var \Shopware\Struct\Customer\Group
     */
    public $fallbackCustomerGroup;

    function __construct($min, $max, Group $currentCustomerGroup, Group $fallbackCustomerGroup)
    {
        $this->min = $min;
        $this->max = $max;
        $this->currentCustomerGroup = $currentCustomerGroup;
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;
    }

    public function getName()
    {
        return 'price';
    }
}