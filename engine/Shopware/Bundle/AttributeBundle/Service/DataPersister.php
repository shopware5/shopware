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
use Doctrine\DBAL\Schema\Column;
use Shopware\Components\Model\DBAL\Types\DateStringType;
use Shopware\Components\Model\DBAL\Types\DateTimeStringType;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\AttributeBundle\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class DataPersister implements DataPersisterInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TableMappingInterface
     */
    private $mapping;

    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;

    /**
     * DataPersister constructor.
     *
     * @param Connection            $connection
     * @param TableMappingInterface $mapping
     * @param DataLoaderInterface   $dataLoader
     */
    public function __construct(Connection $connection, TableMappingInterface $mapping, DataLoaderInterface $dataLoader)
    {
        $this->connection = $connection;
        $this->mapping = $mapping;
        $this->dataLoader = $dataLoader;
    }

    /**
     * Persists the provided data into the provided attribute table.
     * Only attribute tables supported.
     *
     * @param string $table
     * @param array $data
     * @param int|string $foreignKey
     * @throws \Exception
     */
    public function persist($data, $table, $foreignKey)
    {
        if (!$this->mapping->isAttributeTable($table)) {
            throw new \Exception(sprintf("Table %s is no attribute table", $table));
        }
        if (!$foreignKey) {
            throw new \Exception(sprintf("No foreign key provided"));
        }
        $data = $this->filter($table, $data);

        $exists = $this->dataLoader->load($table, $foreignKey);

        if ($exists) {
            $this->update($table, $data, $foreignKey);
        } else {
            $this->create($table, $data, $foreignKey);
        }
    }

    /**
     * @param string $table
     * @param int $sourceForeignKey
     * @param int $targetForeignKey
     * @throws \Exception
     */
    public function cloneAttribute($table, $sourceForeignKey, $targetForeignKey)
    {
        if (!$this->mapping->isAttributeTable($table)) {
            throw new \Exception(sprintf("Table %s is no attribute table", $table));
        }
        if (!$sourceForeignKey) {
            throw new \Exception(sprintf("No foreign key provided"));
        }
        $data = $this->dataLoader->load($table, $sourceForeignKey);

        if (empty($data)) {
            return;
        }

        $this->persist($data, $table, $targetForeignKey);

        $this->cloneAttributeTranslations($table, $sourceForeignKey, $targetForeignKey);
    }

    /**
     * @param string $table
     * @param int $sourceForeignKey
     * @param int $targetForeignKey
     * @throws \Exception
     */
    public function cloneAttributeTranslations($table, $sourceForeignKey, $targetForeignKey)
    {
        if (!$this->mapping->isAttributeTable($table)) {
            throw new \Exception(sprintf("Table %s is no attribute table", $table));
        }
        if (!$sourceForeignKey) {
            throw new \Exception(sprintf("No foreign key provided"));
        }

        $translations = $this->dataLoader->loadTranslations($table, $sourceForeignKey);

        foreach ($translations as $translation) {
            $this->saveTranslation($translation, $targetForeignKey);
        }
    }

    /**
     * @param array $translation
     * @param int $foreignKey
     */
    private function saveTranslation($translation, $foreignKey)
    {
        $query = $this->connection->createQueryBuilder();

        unset($translation['id']);
        $translation['objectkey'] = $foreignKey;

        $query->insert('s_core_translations');
        foreach ($translation as $key => $value) {
            $query->setValue($key, ':' . $key);
            $query->setParameter(':' . $key, $value);
        }

        $query->execute();
    }

    /**
     * @param string $table
     * @param array $data
     * @param int|string $foreignKey
     */
    private function create($table, $data, $foreignKey)
    {
        $query = $this->connection->createQueryBuilder();
        $foreignKeyColumn = $this->mapping->getTableForeignKey($table);

        $data[$foreignKeyColumn] = $foreignKey;
        $query->insert($table);
        foreach ($data as $key => $value) {
            $query->setValue($key, ':' . $key);
            $query->setParameter(':' . $key, $value);
        }
        $query->execute();
    }

    /**
     * Updates an existing attribute
     * @param string $table
     * @param array $data
     * @param int|string $foreignKey
     */
    private function update($table, $data, $foreignKey)
    {
        $query = $this->connection->createQueryBuilder();
        $foreignKeyColumn = $this->mapping->getTableForeignKey($table);
        $query->update($table, 'alias');
        foreach ($data as $column => $value) {
            $query->set('alias.' . $column, ':_' . $column);
            $query->setParameter(':_' . $column, $value);
        }
        $query->where('alias.' . $foreignKeyColumn . ' = :_foreignKey');
        $query->setParameter(':_foreignKey', $foreignKey);
        $query->execute();
    }

    /**
     * @param string $table
     * @param array $data
     * @return array
     * @throws \Exception
     */
    private function filter($table, $data)
    {
        /** @var TableMappingInterface $mapping */
        $columns = $this->mapping->getTableColumns($table);

        $result = [];
        foreach ($columns as $column) {
            if ($this->mapping->isIdentifierColumn($table, $column->getName())) {
                continue;
            }
            if (!array_key_exists($column->getName(), $data)) {
                continue;
            }
            $value = $data[$column->getName()];

            if ($this->isDateColumn($column) && empty($value)) {
                $result[$column->getName()] = 'NULL';
            } else {
                $result[$column->getName()] = $value;
            }
        }

        return $result;
    }

    /**
     * @param Column $column
     * @return bool
     */
    private function isDateColumn(Column $column)
    {
        return ($column->getType() instanceof DateStringType || $column->getType() instanceof DateTimeStringType);
    }
}
