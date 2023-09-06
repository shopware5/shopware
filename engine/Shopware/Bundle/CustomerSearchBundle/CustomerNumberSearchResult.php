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

namespace Shopware\Bundle\CustomerSearchBundle;

use JsonSerializable;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

class CustomerNumberSearchResult implements JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    /**
     * @var BaseCustomer[]
     */
    protected $customers;

    /**
     * @var int
     */
    protected $total;

    /**
     * @param BaseCustomer[] $rows
     * @param int            $total
     */
    public function __construct(array $rows, $total)
    {
        $this->customers = $rows;
        $this->total = $total;
    }

    /**
     * @return BaseCustomer[]
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return string[]
     */
    public function getNumbers()
    {
        return array_map(function (BaseCustomer $customer) {
            return $customer->getNumber();
        }, $this->customers);
    }

    /**
     * @return string[]
     */
    public function getEmails()
    {
        return array_map(function (BaseCustomer $customer) {
            return $customer->getEmail();
        }, $this->customers);
    }

    /**
     * @return int[]
     */
    public function getIds()
    {
        return array_map(function (BaseCustomer $customer) {
            return $customer->getId();
        }, $this->customers);
    }
}
