<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

class CustomerService implements CustomerServiceInterface
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
     * @param AddressGateway  $addressGateway
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
     *
     * @return Customer[]
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
