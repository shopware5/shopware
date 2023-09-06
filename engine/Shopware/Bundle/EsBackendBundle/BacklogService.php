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

namespace Shopware\Bundle\EsBackendBundle;

use Doctrine\DBAL\Connection;

class BacklogService implements BacklogServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function read(int $amount): array
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from('s_es_backend_backlog', 'ba')
            ->setMaxResults($amount)
            ->execute()
            ->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $backlogs): void
    {
        foreach ($backlogs as $backlog) {
            $this->connection->insert('s_es_backend_backlog', $backlog->toArray());
        }
    }

    public function cleanup(array $ids): void
    {
        $this->connection->createQueryBuilder()
            ->delete('s_es_backend_backlog')
            ->where('id IN(:ids)')
            ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY)
            ->execute();
    }

    public function clear(): void
    {
        $this->connection->exec('TRUNCATE TABLE `s_es_backend_backlog`');
    }
}
