<?php

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

class CustomerService
{
    /**
     * @var CustomerGateway
     */
    private $customerGateway;

    /**
     * @var AddressGateway
     */
    private $addressGateway;

    /**
     * @param CustomerGateway $customerGateway
     * @param AddressGateway $addressGateway
     */
    public function __construct(
        CustomerGateway $customerGateway,
        AddressGateway $addressGateway
    ) {
        $this->customerGateway = $customerGateway;
        $this->addressGateway = $addressGateway;
    }

    /**
     * @param int[] $customerIds
     * @return CustomerStruct[]
     */
    public function getList($customerIds)
    {
        $customers = $this->customerGateway->getList($customerIds);

        $addressIds = [];
        foreach ($customers as $customer) {
            $addressIds[] = $customer->getDefaultBillingAddressId();
            $addressIds[] = $customer->getDefaultShippingAddressId();
        }

        $addresses = $this->addressGateway->getList($addressIds);

        foreach ($customers as $id => &$customer) {
            $addressId = $customer->getDefaultBillingAddressId();
            if (array_key_exists($addressId, $addresses)) {
                $customer->setBillingAddress($addresses[$addressId]);
            }

            $addressId = $customer->getDefaultShippingAddressId();
            if (array_key_exists($addressId, $addresses)) {
                $customer->setShippingAddress($addresses[$addressId]);
            }
        }

        return $customers;
    }
}
