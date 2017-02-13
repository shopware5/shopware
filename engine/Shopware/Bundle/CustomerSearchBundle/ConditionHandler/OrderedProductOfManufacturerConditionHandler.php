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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedProductOfManufacturerCondition;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;

class OrderedProductOfManufacturerConditionHandler implements ConditionHandlerInterface
{
    public function supports(ConditionInterface $condition)
    {
        return $condition instanceof OrderedProductOfManufacturerCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query)
    {
        $wheres = [];
        /** @var OrderedProductOfManufacturerCondition $condition */
        foreach ($condition->getManufacturerIds() as $i => $id) {
            $wheres[] = 'manufacturers LIKE :manufacturer' . $i;
            $query->setParameter(':manufacturer' . $i, '%||'.$id.'||%');
        }
        $query->andWhere(implode(' OR ', $wheres));
        return;

//        $query->andWhere(
//            'customer.id IN (
//                SELECT DISTINCT o.userID
//                FROM s_order o
//                INNER JOIN s_order_details d
//                    ON d.orderID = o.id
//                    AND d.modus = 0
//                INNER JOIN s_articles a
//                    ON a.id = d.articleID
//                    AND a.supplierID IN (:OrderedProductOfManufacturerCondition)
//            )'
//        );

        $query->innerJoin(
            'customer',
            's_order',
            'orderedManufacturer',
            'orderedManufacturer.userID = customer.id'
        );

        $query->innerJoin(
            'orderedManufacturer',
            's_order_details',
            'orderedManufacturerDetails',
            'orderedManufacturerDetails.orderID = orderedManufacturer.id
             AND orderedManufacturerDetails.modus = 0'
        );
//
//        $query->innerJoin(
//            'orderedManufacturerDetails',
//            's_articles',
//            'orderedManufacturerMapping',
//            'orderedManufacturerMapping.id = orderedManufacturerDetails.articleID
//            AND orderedManufacturerMapping.supplierID IN (:OrderedProductOfManufacturerCondition)'
//        );

//        /** @var OrderedProductOfManufacturerCondition $condition */
//        $query->setParameter(':OrderedProductOfManufacturerCondition', $condition->getManufacturerIds(), Connection::PARAM_INT_ARRAY);
    }
}
