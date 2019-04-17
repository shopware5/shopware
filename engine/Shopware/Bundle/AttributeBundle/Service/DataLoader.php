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

namespace Shopware\Bundle\AttributeBundle\Service;

use Doctrine\DBAL\Connection;

class DataLoader
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TableMapping
     */
    private $mapping;

    public function __construct(Connection $connection, TableMapping $mapping)
    {
        $this->connection = $connection;
        $this->mapping = $mapping;
    }

    /**
     * @param string $table
     * @param int    $foreignKey
     *
     * @throws \Exception
     *
     * @return array
     */
    public function load($table, $foreignKey)
    {
        if (!$this->mapping->isAttributeTable($table)) {
            throw new \Exception(sprintf('Table %s is no attribute table', $table));
        }

        if (!$foreignKey) {
            throw new \Exception('No foreign key provided');
        }

        /** @var TableMapping $mapping */
        $foreignKeyColumn = $this->mapping->getTableForeignKey($table);

        $query = $this->connection->createQueryBuilder();
        $query->select('alias.*')
            ->from($table, 'alias')
            ->where('alias.' . $foreignKeyColumn . ' = :foreignKey')
            ->setParameter(':foreignKey', $foreignKey)
            ->setFirstResult(0)
            ->setMaxResults(1);

        return $query->execute()->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @param string $table
     * @param int    $foreignKey
     *
     * @throws \Exception
     *
     * @return array[]
     */
    public function loadTranslations($table, $foreignKey)
    {
        if (!$foreignKey) {
            throw new \Exception('No foreign key provided');
        }

        $query = $this->connection->createQueryBuilder();
        $query->select('translation.*');
        $query->from('s_core_translations', 'translation');
        $query->where('translation.objecttype = :type');
        $query->setParameter(':type', $table);
        $query->andWhere('objectkey = :key');
        $query->setParameter(':key', $foreignKey);

        return $query->execute()->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
