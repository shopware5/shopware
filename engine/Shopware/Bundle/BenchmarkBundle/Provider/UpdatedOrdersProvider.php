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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

class UpdatedOrdersProvider extends OrdersProvider
{
    private const NAME = 'updated_orders';

    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param int $batch
     *
     * @return array
     */
    protected function getOrdersBasicData(array $config, $batch)
    {
        $ordersQueryBuilder = $this->dbalConnection->createQueryBuilder();

        $lastOrderId = (int) $config['last_order_id'];
        $lastUpdatedOrdersDate = $config['last_updated_orders_date'];

        return $ordersQueryBuilder->select('orders.*')
            ->from('s_order', 'orders')
            ->where('orders.id <= :lastOrderId')
            ->andWhere('orders.changed > :lastOrdersUpdate')
            ->andWhere('orders.subshopID = :shopId')
            ->andWhere('orders.status != -1')
            ->orderBy('orders.changed', 'ASC')
            ->setMaxResults($batch)
            ->setParameter(':lastOrderId', $lastOrderId)
            ->setParameter(':lastOrdersUpdate', $lastUpdatedOrdersDate)
            ->setParameter(':shopId', $this->shopId)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }
}
