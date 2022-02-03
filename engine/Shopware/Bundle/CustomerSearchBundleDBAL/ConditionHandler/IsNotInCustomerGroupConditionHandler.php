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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CustomerSearchBundle\Condition\IsNotInCustomerGroupCondition;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

class IsNotInCustomerGroupConditionHandler implements ConditionHandlerInterface
{
    public function supports(ConditionInterface $condition): bool
    {
        return $condition instanceof IsNotInCustomerGroupCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query): void
    {
        $this->addCondition($condition, $query);
    }

    private function addCondition(IsNotInCustomerGroupCondition $condition, QueryBuilder $query): void
    {
        $query->andWhere('customer.customer_group_id NOT IN (:IsNotInCustomerGroupCondition)');
        $query->setParameter(
            ':IsNotInCustomerGroupCondition',
            $condition->getCustomerGroupIds(),
            Connection::PARAM_INT_ARRAY
        );
    }
}
