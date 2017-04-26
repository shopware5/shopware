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



use Shopware\Components\CustomerStream\CustomerInterestsGateway;
use Shopware\Components\CustomerStream\CustomerOrderGateway;
use Shopware\Bundle\StoreFrontBundle\Service\Core\CustomerService;

class CustomerProvider implements CustomerProviderInterface
{
    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var \Shopware\Components\CustomerStream\CustomerOrderGateway
     */
    private $customerOrderGateway;

    /**
     * @var CustomerInterestsGateway
     */
    private $customerInterestsGateway;

    /**
     * @param CustomerService          $customerService
     * @param \Shopware\Components\CustomerStream\CustomerOrderGateway     $customerOrderGateway
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
        $customers = $this->customerService->getList($customerIds);

        $orders = $this->customerOrderGateway->getList($customerIds);

        $interests = $this->customerInterestsGateway->getInterests($customerIds, 360);

        $analyzedCustomers = [];
        foreach ($customers as $id => $customer) {
            $analyzedCustomer = AnalyzedCustomer::createFromCustomer($customer);
            $analyzedCustomers[$id] = $analyzedCustomer;

            $analyzedCustomer->setOrderInformation($orders[$id]);

            if (array_key_exists($id, $interests)) {
                $analyzedCustomer->setInterests($interests[$id]);
            }
        }

        return $analyzedCustomers;
    }
}
