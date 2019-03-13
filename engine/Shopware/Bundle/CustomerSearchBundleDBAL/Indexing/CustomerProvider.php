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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\Indexing;

use Shopware\Bundle\StoreFrontBundle\Service\CustomerServiceInterface;

class CustomerProvider implements CustomerProviderInterface
{
    /**
     * @var CustomerServiceInterface
     */
    private $customerService;

    /**
     * @var CustomerOrderGateway
     */
    private $customerOrderGateway;

    public function __construct(
        CustomerServiceInterface $customerService,
        CustomerOrderGateway $customerOrderGateway
    ) {
        $this->customerService = $customerService;
        $this->customerOrderGateway = $customerOrderGateway;
    }

    public function get($customerIds)
    {
        $customers = $this->customerService->getList($customerIds);

        $orders = $this->customerOrderGateway->getList($customerIds);

        $analyzedCustomers = [];
        foreach ($customers as $id => $customer) {
            $analyzedCustomer = AnalyzedCustomer::createFromCustomer($customer);
            $analyzedCustomers[$id] = $analyzedCustomer;

            $analyzedCustomer->setOrderInformation($orders[$id]);
        }

        return $analyzedCustomers;
    }
}
