<?php

namespace Shopware\Gateway\Search\Sorting;

use Shopware\Gateway\Search\Sorting;
use Shopware\Struct\Customer\Group;

class Price extends Sorting
{
    /**
     * @var \Shopware\Struct\Customer\Group
     */
    public $currentCustomerGroup;

    /**
     * @var \Shopware\Struct\Customer\Group
     */
    public $fallbackCustomerGroup;

    /**
     * @param string $direction
     * @param Group $currentCustomerGroup
     * @param Group $fallbackCustomerGroup
     */
    function __construct(
        $direction = 'ASC',
        Group $currentCustomerGroup,
        Group $fallbackCustomerGroup
    ) {
        $this->direction = $direction;
        $this->currentCustomerGroup = $currentCustomerGroup;
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;
    }

    function getName()
    {
        return 'prices';
    }


}