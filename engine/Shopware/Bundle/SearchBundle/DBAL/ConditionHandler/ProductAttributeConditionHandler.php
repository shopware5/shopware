<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Bundle\SearchBundle\DBAL\ConditionHandler;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\DBAL\ConditionHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\SearchBundle\DBAL\QueryBuilder;

class ProductAttributeConditionHandler implements ConditionHandlerInterface
{
    /**
     * Checks if the passed condition can be handled by this class.
     *
     * @param ConditionInterface $condition
     * @return bool
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return ($condition instanceof ProductAttributeCondition);
    }

    /**
     * Handles the passed condition object.
     * Extends the provided query builder with the specify conditions.
     * Should use the andWhere function, otherwise other conditions would be overwritten.
     *
     * @param ConditionInterface|ProductAttributeCondition $condition
     * @param QueryBuilder $query
     * @param Context $context
     * @throws \Exception
     * @return void
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        Context $context
    ) {
        if (!$condition->getField()) {
            throw new \Exception('ProductAttributeCondition class requires a defined attribute field!');
        }

        if (!$condition->getOperator()) {
            throw new \Exception('ProductAttributeCondition class requires a defined operator!');
        }

        $placeholder = ':' . $condition->getField();

        switch (true) {
            case ($condition->getValue() === null):
                if ($condition->getOperator() === ProductAttributeCondition::OPERATOR_EQ) {
                    $query->andWhere('productAttribute.' . $condition->getField() . ' IS NULL');
                } else {
                    $query->andWhere('productAttribute.' . $condition->getField() . ' IS NOT NULL');
                }
                break;

            case ($condition->getOperator() === ProductAttributeCondition::OPERATOR_IN):
                $query->andWhere('productAttribute' . $condition->getField() . ' IN ('. $placeholder . ')');
                $query->setParameter($placeholder, $condition->getValue(), Connection::PARAM_STR_ARRAY);
                break;

            case ($condition->getOperator() === ProductAttributeCondition::OPERATOR_CONTAINS):
                $query->andWhere('productAttribute.' . $condition->getField() . ' LIKE ' . $placeholder);
                $query->setParameter($placeholder, '%' . $condition->getValue() . '%');
                break;

            case ($condition->getOperator() === ProductAttributeCondition::OPERATOR_START_WITH):
                $query->andWhere('productAttribute.' . $condition->getField() . ' LIKE ' . $placeholder);
                $query->setParameter($placeholder, $condition->getValue() . '%');
                break;

            case ($condition->getOperator() === ProductAttributeCondition::OPERATOR_ENDS_WITH):
                $query->andWhere('productAttribute.' . $condition->getField() . ' LIKE ' . $placeholder);
                $query->setParameter($placeholder, '%' . $condition->getValue());
                break;

            default:
                $query->andWhere('productAttribute.' . $condition->getField() . ' ' . $condition->getOperator() . ' ' . $placeholder);
                $query->setParameter($placeholder, $condition->getValue());
                break;
        }
    }
}