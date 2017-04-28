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

use Shopware\Bundle\CustomerSearchBundle\Condition\AgeCondition;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

class AgeConditionHandler implements ConditionHandlerInterface
{
    public function supports(ConditionInterface $condition)
    {
        return $condition instanceof AgeCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query)
    {
        /** @var AgeCondition $condition */
        if (!$condition->getOperator()) {
            throw new \Exception('AgeCondition class requires a defined operator!');
        }

        if (!$condition->getOperator()) {
            throw new \Exception('AgeCondition class requires a defined value!');
        }

        switch (true) {
            case $condition->getOperator() === AgeCondition::OPERATOR_BETWEEN:
                $value = $condition->getValue();

                if (isset($value['min'])) {
                    $query->andWhere('customer.age >= :Min')
                        ->setParameter(':Min', $value['min']);
                }

                if (isset($value['max'])) {
                    $query->andWhere('customer.age <= :Max')
                        ->setParameter(':Max', $value['max']);
                }

                break;
            default:
                $query->andWhere('customer.age ' . $condition->getOperator() . ' :Value');
                $query->setParameter(':Value', $condition->getValue());
                break;
        }
    }
}
