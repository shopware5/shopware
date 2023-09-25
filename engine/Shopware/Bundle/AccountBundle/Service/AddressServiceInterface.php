<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\AccountBundle\Service;

use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

interface AddressServiceInterface
{
    /**
     * @return void
     */
    public function create(Address $address, Customer $customer);

    /**
     * @return void
     */
    public function update(Address $address);

    /**
     * @return void
     */
    public function delete(Address $address);

    /**
     * Sets the address to the default billing address in the customer model
     *
     * @return void
     */
    public function setDefaultBillingAddress(Address $address);

    /**
     * Sets the address to the default shipping address in the customer model
     *
     * @return void
     */
    public function setDefaultShippingAddress(Address $address);
}
