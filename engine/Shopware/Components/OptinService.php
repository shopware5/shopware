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

        $sql = <<<'SQL'
            INSERT INTO s_core_optin
            (type, datum, hash, data)
            VALUES
           (:type, :datum, :hash, :data)
SQL;

        $serializedData = serialize($data);
        $newDatum = date('Y-m-d H:i:s', time() + $duration);

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            ':type' => $type,
            ':datum' => $newDatum,
            ':hash' => $hash,
            ':data' => $serializedData,
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

        $sql = <<<'SQL'
            SELECT data
            FROM s_core_optin AS optIn
            WHERE optIn.hash = :hash
                AND optIn.type = :type
                AND optIn.datum >= :currentDate
SQL;

        $currentDate = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            ':type' => $type,
            ':hash' => $hash,
            ':currentDate' => $currentDate,
        ]);

        $data = $statement->fetchColumn();

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

        $sql = <<<'SQL'
            DELETE FROM s_core_optin
            WHERE hash = :hash AND type = :type
SQL;

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            ':type' => $type,
            ':hash' => $hash,
        ]);
    }
}
