<?php

namespace Shopware\Bundle\CustomerSearchBundle\CustomerStream;

use Shopware\Bundle\CustomerSearchBundle\Gateway\CustomerInterestsGateway;
use Shopware\Bundle\CustomerSearchBundle\Gateway\CustomerOrderGateway;
use Shopware\Bundle\CustomerSearchBundle\Gateway\CustomerService;

class CustomerProvider
{
    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var CustomerOrderGateway
     */
    private $customerOrderGateway;

    /**
     * @var CustomerInterestsGateway
     */
    private $customerInterestsGateway;

    /**
     * @param CustomerService $customerService
     * @param CustomerOrderGateway $customerOrderGateway
     * @param CustomerInterestsGateway $customerInterestsGateway
     */
    public function __construct(
        CustomerService $customerService,
        CustomerOrderGateway $customerOrderGateway,
        CustomerInterestsGateway $customerInterestsGateway
    ) {
        $this->customerService = $customerService;
        $this->customerOrderGateway = $customerOrderGateway;
        $this->customerInterestsGateway = $customerInterestsGateway;
    }

    public function get($customerIds)
    {
        /**
         * all country ids
         * ordered at weekday
         * ordered in shop
         * ordered on device
         * nested categories
         * deliveries
         * payments
         */

        $customers = $this->customerService->getList($customerIds);

        $orders = $this->customerOrderGateway->getList($customerIds);

        $interests = $this->customerInterestsGateway->getList($customerIds);

        $analyzedCustomers = [];
        foreach ($customers as $id => $customer) {
            $analyzedCustomer = AnalyzedCustomerStruct::createFromCustomer($customer);
            $analyzedCustomer->setOrderInformation($orders[$id]);
            if (array_key_exists($id, $interests)) {
                $analyzedCustomer->setInterests($interests[$id]);
            }
            $analyzedCustomers[$id] = $analyzedCustomer;
        }
        return $analyzedCustomers;
    }
}
