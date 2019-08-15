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

namespace Shopware\Bundle\ESIndexingBundle;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;

class BacklogReader implements BacklogReaderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastBacklogId()
    {
        $value = $this->connection->createQueryBuilder()
            ->select('value')
            ->from('s_core_config_elements', 'elements')
            ->where('elements.name = :name')
            ->setParameter(':name', 'lastBacklogId')
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        return unserialize($value, ['allowed_classes' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastBacklogId($lastId)
    {
        $this->connection->executeUpdate(
            "UPDATE s_core_config_elements SET value = :value WHERE name = 'lastBacklogId'",
            [':value' => serialize($lastId)]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function read($lastId, $limit)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['id', 'event', 'payload', 'time'])
            ->from('s_es_backlog', 'backlog')
            ->andWhere('backlog.id > :lastId')
            ->orderBy('backlog.id', 'ASC')
            ->setParameter(':lastId', $lastId)
            ->setMaxResults($limit);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($data as $row) {
            $backlog = new Backlog(
                $row['event'],
                json_decode($row['payload'], true),
                $row['time'],
                (int) $row['id']
            );
            $result[] = $backlog;
        }

        return $result;
    }
}
