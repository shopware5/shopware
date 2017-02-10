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


namespace Shopware\Bundle\CustomerSearchBundle\ConditionHandler\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class AggregatedOrderTable
{
    const JOINED_STATE = 'AggregatedOrderTable';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return QueryBuilder
     */
    public function getQuery()
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect([
            'orders.userID as customer_id',
            'COUNT(orders.id) count_orders',
            'SUM(orders.invoice_amount) as invoice_amount_sum',
            'AVG(orders.invoice_amount) as invoice_amount_avg',
            'MIN(orders.invoice_amount) as invoice_amount_min',
            'MAX(orders.invoice_amount) as invoice_amount_max',
            'MIN(orders.ordertime) as first_order_time',
            'MAX(orders.ordertime) as last_order_time',
        ]);
        $query->from('s_order', 'orders');
        $query->where('orders.ordernumber != 0');
//        $query->andWhere('orders.status = 2');
        $query->groupBy('orders.userID');

        return $query;
    }
}
