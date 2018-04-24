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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;

class LocalOrdersProvider implements BenchmarkProviderInterface
{
    /**
     * @var Connection
     */
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return 'orders';
    }

    public function getBenchmarkData()
    {
        return $this->getOrderDataByDay();
    }

    /**
     * @return array
     */
    private function getOrderDataByDay()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $result = $queryBuilder->select([
                'DATE(orders.ordertime) as orderTime',
                'DAYNAME(orders.ordertime) as dayOfWeek',
                'SUM(orders.invoice_amount/currencyFactor) as orderAmount',
                'SUM(orders.invoice_amount_net/currencyFactor) as orderAmountNet',
                'COUNT(orders.id) as totalOrders',
            ])
            ->from('s_order', 'orders')
            ->where('orders.status != 4')
            ->andWhere('orders.status != -1')
            ->orderBy('orders.ordertime', 'asc')
            ->groupBy('DATE(orders.ordertime)')
            ->setMaxResults(365)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

        $result = array_map(function ($item) {
            $item['orderAmount'] = round((float) $item['orderAmount'], 2);
            $item['orderAmountNet'] = round((float) $item['orderAmountNet'], 2);
            $item['totalOrders'] = (int) $item['totalOrders'];

            return $item;
        }, $result);

        return $result;
    }
}
