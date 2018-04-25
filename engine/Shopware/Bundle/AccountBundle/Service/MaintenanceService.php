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

namespace Shopware\Bundle\AccountBundle\Service;

use Doctrine\DBAL\Connection;

class MaintenanceService implements MaintenanceServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MaintenanceService constructor.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanupGuestUsers($months, $orderAmount)
    {
        $qb = $this->connection->createQueryBuilder();

        $result = $qb->select([
            'user.id',
            'COUNT(_order.id) AS orderAmount',
            'TIMESTAMPDIFF(MONTH,user.firstlogin,NOW()) AS monthDiff',
        ])
        ->from('s_user', 'user')
        ->leftJoin('user', 's_order', '_order', 'user.id = _order.userID')
        ->groupBy('user.id')
        ->having('orderAmount >= :orderAmount')
        ->andHaving('monthDiff >= :months')
        ->setParameter(':months', $months)
        ->setParameter(':orderAmount', (int) $orderAmount)
        ->execute()
        ->fetchAll();

        $users = [];

        array_map(function ($value) use (&$users) {
            $users[] = (int) $value['id'];
        }, $result);

        $qb->delete('s_user')
            ->where('id IN (:users)')
            ->innerJoin('s_order')
            ->setParameter(':users', $users, Connection::PARAM_INT_ARRAY)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function cleanupCanceledOrders($months)
    {
        $qb = $this->connection->createQueryBuilder();

        $canceledOrders = $qb->select([
            '_order.id',
            'TIMESTAMPDIFF(MONTH,ordertime,NOW()) AS monthDiff',
        ])
        ->from('s_order', '_order')
        ->having('monthDiff >= :months')
        ->setParameter(':months', (int) $months, \PDO::PARAM_INT)
        ->execute()
        ->fetchAll();

        $orders = [];

        array_map(function ($value) use (&$orders) {
            $orders[] = (int) $value['id'];
        }, $canceledOrders);

        $qb->delete('s_order')
            ->where('id IN (:orders)')
            ->setParameter(':orders', $orders, Connection::PARAM_INT_ARRAY)
            ->execute();
    }
}
