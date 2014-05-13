<?php

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;
use Shopware\Struct\Customer\Group;

class Price extends Facet
{
    /**
     * @var array
     */
    public $range;

    /**
     * @var \Shopware\Struct\Customer\Group
     */
    public $currentCustomerGroup;

    /**
     * @var \Shopware\Struct\Customer\Group
     */
    public $fallbackCustomerGroup;

    /**
     * @param Group $currentCustomerGroup
     * @param Group $fallbackCustomerGroup
     */
    function __construct(Group $currentCustomerGroup, Group $fallbackCustomerGroup)
    {
        $this->currentCustomerGroup = $currentCustomerGroup;
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;
    }

    public function getName()
    {
        return 'price';
    }

}