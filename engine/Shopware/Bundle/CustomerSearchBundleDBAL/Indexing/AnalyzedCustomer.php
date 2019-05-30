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

use Shopware\Bundle\StoreFrontBundle\Struct\Customer;

class AnalyzedCustomer extends Customer
{
    /**
     * @var CustomerOrder
     */
    protected $orderInformation;

    /**
     * @return AnalyzedCustomer
     */
    public static function createFromCustomer(Customer $customer)
    {
        $self = new self();
        foreach ($customer as $property => $key) {
            $self->$property = $key;
        }

        return $self;
    }

    /**
     * @return \Shopware\Bundle\CustomerSearchBundleDBAL\Indexing\CustomerOrder
     */
    public function getOrderInformation()
    {
        return $this->orderInformation;
    }

    /**
     * @param CustomerOrder $orderInformation
     */
    public function setOrderInformation($orderInformation)
    {
        $this->orderInformation = $orderInformation;
    }
}
