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

use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedProductOfManufacturerCondition;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

class OrderedProductOfManufacturerConditionHandler implements ConditionHandlerInterface
{
    public function supports(ConditionInterface $condition)
    {
        return $condition instanceof OrderedProductOfManufacturerCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query)
    {
        $this->addCondition($condition, $query);
    }

    private function addCondition(OrderedProductOfManufacturerCondition $condition, QueryBuilder $query): void
    {
        $wheres = [];
        foreach ($condition->getManufacturerIds() as $i => $id) {
            $wheres[] = 'customer.ordered_products_of_manufacturer LIKE :manufacturer' . $i;
            $query->setParameter(':manufacturer' . $i, '%|' . $id . '|%');
        }
        $query->andWhere(implode(' OR ', $wheres));
    }
}
