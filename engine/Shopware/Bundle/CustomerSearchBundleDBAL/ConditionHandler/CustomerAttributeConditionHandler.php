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

use Shopware\Bundle\CustomerSearchBundle\Condition\CustomerAttributeCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

class CustomerAttributeConditionHandler
{
    public function supports(ConditionInterface $condition)
    {
        return $condition instanceof CustomerAttributeCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query)
    {
        /** @var CustomerAttributeCondition $condition */
        if (!$condition->getField()) {
            throw new \Exception('CustomerAttributeCondition class requires a defined attribute field!');
        }

        if (!$condition->getOperator()) {
            throw new \Exception('CustomerAttributeCondition class requires a defined operator!');
        }

        $placeholder = ':' . $condition->getField();
        $field = 'customerAttribute.' . $condition->getField();

        switch (true) {
            case $condition->getValue() === null:
                if ($condition->getOperator() === CustomerAttributeCondition::OPERATOR_EQ) {
                    $query->andWhere($field . ' IS NULL');
                } else {
                    $query->andWhere($field . ' IS NOT NULL');
                }
                break;

            case $condition->getOperator() === CustomerAttributeCondition::OPERATOR_IN:
                $query->andWhere($field . ' IN (' . $placeholder . ')');
                $query->setParameter($placeholder, $condition->getValue(), Connection::PARAM_STR_ARRAY);
                break;

            case $condition->getOperator() === CustomerAttributeCondition::OPERATOR_CONTAINS:
                $query->andWhere($field . ' LIKE ' . $placeholder);
                $query->setParameter($placeholder, '%' . $condition->getValue() . '%');
                break;

            case $condition->getOperator() === CustomerAttributeCondition::OPERATOR_BETWEEN:
                $value = $condition->getValue();

                if (isset($value['min'])) {
                    $query->andWhere($field . ' >= ' . $placeholder . 'Min')
                        ->setParameter($placeholder . 'Min', $value['min']);
                }

                if (isset($value['max'])) {
                    $query->andWhere($field . ' <= ' . $placeholder . 'Max')
                        ->setParameter($placeholder . 'Max', $value['max']);
                }

                break;
            case $condition->getOperator() === CustomerAttributeCondition::OPERATOR_STARTS_WITH:
                $query->andWhere($field . ' LIKE ' . $placeholder);
                $query->setParameter($placeholder, $condition->getValue() . '%');
                break;

            case $condition->getOperator() === CustomerAttributeCondition::OPERATOR_ENDS_WITH:
                $query->andWhere($field . ' LIKE ' . $placeholder);
                $query->setParameter($placeholder, '%' . $condition->getValue());
                break;

            default:
                $query->andWhere($field . ' ' . $condition->getOperator() . ' ' . $placeholder);
                $query->setParameter($placeholder, $condition->getValue());
                break;
        }
    }
}
