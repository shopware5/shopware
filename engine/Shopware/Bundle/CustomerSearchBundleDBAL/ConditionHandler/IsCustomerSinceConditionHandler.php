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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\Condition\IsCustomerSinceCondition;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

class IsCustomerSinceConditionHandler implements ConditionHandlerInterface
{
    public function supports(ConditionInterface $condition)
    {
        return $condition instanceof IsCustomerSinceCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query)
    {
        $this->addCondition($condition, $query);
    }

    private function addCondition(IsCustomerSinceCondition $condition, QueryBuilder $query): void
    {
        switch ($condition->getOperator()) {
            case ConditionInterface::OPERATOR_EQ:
                $query->andWhere('customer.firstlogin = :IsCustomerSinceCondition');
                break;
            case ConditionInterface::OPERATOR_NEQ:
                $query->andWhere('customer.firstlogin != :IsCustomerSinceCondition');
                break;
            case ConditionInterface::OPERATOR_LT:
                $query->andWhere('customer.firstlogin < :IsCustomerSinceCondition');
                break;
            case ConditionInterface::OPERATOR_LTE:
                $query->andWhere('customer.firstlogin <= :IsCustomerSinceCondition');
                break;
            case ConditionInterface::OPERATOR_GT:
                $query->andWhere('customer.firstlogin > :IsCustomerSinceCondition');
                break;
            default:
                $query->andWhere('customer.firstlogin >= :IsCustomerSinceCondition');
        }

        $query->setParameter(':IsCustomerSinceCondition', $condition->getCustomerSince()->format('Y-m-d'));
    }
}
