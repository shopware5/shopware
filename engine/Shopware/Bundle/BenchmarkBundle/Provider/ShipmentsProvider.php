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

class ShipmentsProvider implements BenchmarkProviderInterface
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
        return 'shipments';
    }

    /**
     * @return array
     */
    public function getBenchmarkData()
    {
        return [
            'shipments' => $this->getShipments(),
            'shipmentUsages' => $this->getShipmentUsages(),
        ];
    }

    /**
     * @return array
     */
    private function getShipments()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $shippingCosts = $queryBuilder->select('dispatch.name, MIN(costs.value) as minPrice, MAX(costs.value) as maxPrice')
            ->from('s_premium_dispatch', 'dispatch')
            ->innerJoin('dispatch', 's_premium_shippingcosts', 'costs', 'dispatch.id = costs.dispatchID')
            ->groupBy('dispatch.id')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

        return array_map(function ($shippingCost) {
            $shippingCost['minPrice'] = (float) $shippingCost['minPrice'];
            $shippingCost['maxPrice'] = (float) $shippingCost['maxPrice'];

            return $shippingCost;
        }, $shippingCosts);
    }

    /**
     * @return array
     */
    private function getShipmentUsages()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('orders.dispatchID, dispatches.name, COUNT(orders.id) as usages')
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_premium_dispatch', 'dispatches', 'dispatches.id = orders.dispatchID')
            ->groupBy('orders.dispatchID')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);
    }
}
