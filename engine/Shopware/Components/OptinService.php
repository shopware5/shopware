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

namespace Shopware\Components;

use Doctrine\DBAL\Connection;

class OptinService implements OptinServiceInterface
{
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
     * {@inheritdoc}
     */
    public function add($type, $duration, array $data)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('$type has to be of type string');
        }

        if (!is_numeric($duration)) {
            throw new \InvalidArgumentException('$duration has to be of type integer');
        }

        $hash = Random::getAlphanumericString(32);

        $this->connection->insert('s_core_optin', [
            'type' => $type,
            'datum' => date('Y-m-d H:i:s', time() + $duration),
            'hash' => $hash,
            'data' => serialize($data),
        ]);

        return $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function get($type, $hash)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('$type has to be of type string');
        }

        if (!is_string($hash)) {
            throw new \InvalidArgumentException('$hash has to be of type string');
        }

        $data = $this->connection->createQueryBuilder()->from('s_core_optin', 'optin')
            ->select('data')
            ->where('optin.hash = :hash')
            ->andWhere('optin.type = :type')
            ->andWhere('optin.datum >= :currentDate')
            ->setParameter('hash', $hash)
            ->setParameter('type', $type)
            ->setParameter('currentDate', date('Y-m-d H:i:s'))
            ->execute()
            ->fetchColumn();

        if (empty($data)) {
            return null;
        }

        return unserialize($data, ['allowed_classes' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($type, $hash)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('$type has to be of type string');
        }

        if (!is_string($hash)) {
            throw new \InvalidArgumentException('$hash has to be of type string');
        }

        $this->connection->createQueryBuilder()
            ->delete('s_core_optin')
            ->where('hash = :hash')
            ->andWhere('type = :type')
            ->setParameter('hash', $hash)
            ->setParameter('type', $type)
            ->execute();
    }
}
