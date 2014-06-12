<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;

class CustomerGroup extends Condition
{
    /**
     * @var array
     */
    private $customerGroupIds;

    /**
     * @param array $customerGroupIds
     */
    function __construct(array $customerGroupIds)
    {
        $this->customerGroupIds = $customerGroupIds;
    }

    public function getName()
    {
        return 'customer_group';
    }

    /**
     * @return int
     */
    public function getCustomerGroupIds()
    {
        return $this->customerGroupIds;
    }
}
