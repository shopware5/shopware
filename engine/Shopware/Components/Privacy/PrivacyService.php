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

namespace Shopware\Components\Privacy;

use Doctrine\DBAL\Connection;

class PrivacyService implements PrivacyServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MaintenanceService constructor
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanupGuestUsers($months)
    {
        $qb = $this->connection->createQueryBuilder();

        $users = $qb->select([
            'user.id',
            '_order.status',
            'TIMESTAMPDIFF(MONTH,user.firstlogin,NOW()) AS monthDiff',
        ])
        ->from('s_user', 'user')
        ->leftJoin('user', 's_order', '_order', 'user.id = _order.userID')
        ->where('user.accountmode = 1')
        ->groupBy('user.id,_order.status')
        ->having('monthDiff >= :months')
        ->setParameter(':months', $months)
        ->execute()
        ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);

        // filter customer with valid orders(order_status not null and not -1)
        foreach ($users as $id => $stati) {
            $validOrder = false;
            foreach ($stati as $status) {
                if ($status !== null && (int) $status !== -1) {
                    $validOrder = true;
                    break;
                }
            }
            if ($validOrder) {
                unset($users[$id]);
            }
        }

        $this->deleteFromTable('s_user', array_keys($users));
    }

    /**
     * {@inheritdoc}
     */
    public function cleanupCanceledOrders($months)
    {
        // Select canceled orders
        $qb = $this->connection->createQueryBuilder();

        $canceledOrders = $qb->select([
            '_order.id',
            'TIMESTAMPDIFF(MONTH,ordertime,NOW()) AS monthDiff',
        ])
        ->from('s_order', '_order')
        ->where('_order.status = -1')
        ->having('monthDiff >= :months')
        ->setParameter(':months', (int) $months, \PDO::PARAM_INT)
        ->execute()
        ->fetchAll(\PDO::FETCH_COLUMN);

        $this->deleteFromTable('s_order', $canceledOrders);

        // Select canceled baskets
        $qb = $this->connection->createQueryBuilder();

        $canceledBaskets = $qb->select([
            '_basket.id',
            'TIMESTAMPDIFF(MONTH,datum,NOW()) AS monthDiff',
        ])
            ->from('s_order_basket', '_basket')
            ->having('monthDiff >= :months')
            ->setParameter(':months', (int) $months, \PDO::PARAM_INT)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $this->deleteFromTable('s_order_basket', $canceledBaskets);
    }

    /**
     * Deletes rows with the given $ids from $table
     *
     * @param string $table
     * @param int[]  $ids
     */
    private function deleteFromTable($table, $ids)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->delete($table)
            ->where('id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->execute();
    }
}
