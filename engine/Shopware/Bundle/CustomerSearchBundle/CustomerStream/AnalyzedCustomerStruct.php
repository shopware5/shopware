<?php

namespace Shopware\Bundle\CustomerSearchBundle\CustomerStream;

use Shopware\Bundle\CustomerSearchBundle\Gateway\CustomerOrderStruct;
use Shopware\Bundle\CustomerSearchBundle\Gateway\CustomerStruct;
use Shopware\Bundle\CustomerSearchBundle\Gateway\InterestsStruct;

class AnalyzedCustomerStruct extends CustomerStruct
{
    /**
     * @var CustomerOrderStruct
     */
    protected $orderInformation;

    /**
     * @var InterestsStruct[]
     */
    protected $interests = [];

    /**
     * @param CustomerStruct $customer
     * @return AnalyzedCustomerStruct
     */
    public static function createFromCustomer(CustomerStruct $customer)
    {
        $self = new self();
        foreach ($customer as $property => $key) {
            $self->$property = $key;
        }
        return $self;
    }

    /**
     * @return CustomerOrderStruct
     */
    public function getOrderInformation()
    {
        return $this->orderInformation;
    }

    /**
     * @param CustomerOrderStruct $orderInformation
     */
    public function setOrderInformation($orderInformation)
    {
        $this->orderInformation = $orderInformation;
    }

    /**
     * @return InterestsStruct[]
     */
    public function getInterests()
    {
        return $this->interests;
    }

    /**
     * @param InterestsStruct[] $interests
     */
    public function setInterests($interests)
    {
        $this->interests = $interests;
    }
}
