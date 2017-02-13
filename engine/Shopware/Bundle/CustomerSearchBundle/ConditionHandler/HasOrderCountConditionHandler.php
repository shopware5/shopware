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

namespace Shopware\Bundle\CustomerSearchBundle\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\Common\AggregatedOrderTable;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\CustomerSearchBundle\Condition\HasOrderCountCondition;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;

class HasOrderCountConditionHandler implements ConditionHandlerInterface
{
    /**
     * @var AggregatedOrderTable
     */
    private $aggregatedOrderTable;

    /**
     * @param AggregatedOrderTable $aggregatedOrderTable
     */
    public function __construct(AggregatedOrderTable $aggregatedOrderTable)
    {
        $this->aggregatedOrderTable = $aggregatedOrderTable;
    }

    public function supports(ConditionInterface $condition)
    {
        return $condition instanceof HasOrderCountCondition;
    }

    /**
     * @param ConditionInterface $condition
     * @param QueryBuilder $query
     */
    public function handle(ConditionInterface $condition, QueryBuilder $query)
    {
        $query->andWhere('customer.count_orders >= :HasOrderCountCondition');

        /** @var HasOrderCountCondition $condition */
        $query->setParameter(':HasOrderCountCondition', $condition->getMinimumOrderCount());
        return;

        if (!$query->hasState(AggregatedOrderTable::JOINED_STATE)) {
            $orderTable = $this->aggregatedOrderTable->getQuery();

            $query->innerJoin(
                'customer',
                '( '. $orderTable->getSQL() .' )',
                'order_aggregation',
                'order_aggregation.customer_id = customer.id'
            );
            $query->addState(AggregatedOrderTable::JOINED_STATE);
        }

        $query->andWhere('order_aggregation.count_orders >= :HasOrderCountCondition');

        /** @var HasOrderCountCondition $condition */
        $query->setParameter(':HasOrderCountCondition', $condition->getMinimumOrderCount());
    }
}
