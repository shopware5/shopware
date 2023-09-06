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

use Shopware\Bundle\CustomerSearchBundle\Condition\NotOrderedWithDeliveryCondition;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

class NotOrderedWithDeliveryConditionHandler implements ConditionHandlerInterface
{
    public function supports(ConditionInterface $condition): bool
    {
        return $condition instanceof NotOrderedWithDeliveryCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query): void
    {
        $this->addCondition($condition, $query);
    }

    private function addCondition(NotOrderedWithDeliveryCondition $condition, QueryBuilder $query): void
    {
        $likes = [];
        foreach ($condition->getDispatchIds() as $i => $number) {
            $likes[] = 'ordered_with_deliveries LIKE :delivery' . $i;
        }

        $query->where('customernumber NOT IN
                (
                    SELECT customernumber
                    FROM s_customer_search_index
                    WHERE ' . implode(' OR ', $likes) . '
                )'
        );
        foreach ($condition->getDispatchIds() as $i => $number) {
            $query->setParameter(':delivery' . $i, '%|' . $number . '|%');
        }
    }
}
