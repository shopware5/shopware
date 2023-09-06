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

namespace Shopware\Bundle\CustomerSearchBundle\Condition;

use DateTime;
use Shopware\Bundle\SearchBundle\ConditionInterface;

class IsCustomerSinceCondition implements ConditionInterface
{
    private const NAME = 'IsCustomerSinceCondition';

    /**
     * @var DateTime
     */
    protected $customerSince;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @param DateTime|string $customerSince
     * @param string          $operator
     */
    public function __construct($customerSince, $operator = ConditionInterface::OPERATOR_GTE)
    {
        if (!$customerSince instanceof DateTime) {
            $customerSince = new DateTime($customerSince);
        }
        $this->customerSince = $customerSince;
        $this->operator = $operator;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return DateTime
     */
    public function getCustomerSince()
    {
        return $this->customerSince;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }
}
