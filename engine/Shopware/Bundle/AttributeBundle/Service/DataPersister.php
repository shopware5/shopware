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

namespace Shopware\Bundle\AttributeBundle\Service;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Exception;
use PDO;
use Shopware\Components\Model\DBAL\Types\DateStringType;
use Shopware\Components\Model\DBAL\Types\DateTimeStringType;

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

    public function __construct(Connection $connection, TableMappingInterface $mapping, DataLoaderInterface $dataLoader)
    {
        $this->connection = $connection;
        $this->mapping = $mapping;
        $this->dataLoader = $dataLoader;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function persist($data, $table, $foreignKey)
    {
        if (!$this->mapping->isAttributeTable($table)) {
            throw new Exception(sprintf('Table %s is no attribute table', $table));
        }
        if (!$foreignKey) {
            throw new Exception('No foreign key provided');
        }
        $data = $this->filter($table, $data);

        $exists = $this->dataLoader->load($table, $foreignKey);

        if (!$exists) {
            $this->create($table, $data, $foreignKey);

            return;
        }

        if (empty($data)) {
            return;
        }

        $this->update($table, $data, $foreignKey);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function cloneAttribute($table, $sourceForeignKey, $targetForeignKey)
    {
        if (!$this->mapping->isAttributeTable($table)) {
            throw new Exception(sprintf('Table %s is no attribute table', $table));
        }
        if (!$sourceForeignKey) {
            throw new Exception('No foreign key provided');
        }
        $data = $this->dataLoader->load($table, $sourceForeignKey);

        if (empty($data)) {
            return;
        }

        $this->persist($data, $table, $targetForeignKey);

        $this->cloneAttributeTranslations($table, $sourceForeignKey, $targetForeignKey);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function cloneAttributeTranslations($table, $sourceForeignKey, $targetForeignKey)
    {
        if (!$this->mapping->isAttributeTable($table)) {
            throw new Exception(sprintf('Table %s is no attribute table', $table));
        }
        if (!$sourceForeignKey) {
            throw new Exception('No foreign key provided');
        }

        $translations = $this->dataLoader->loadTranslations($table, $sourceForeignKey);

        foreach ($translations as $translation) {
            $this->saveTranslation($translation, $targetForeignKey);
        }
    }

    /**
     * @param array $translation
     * @param int   $foreignKey
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
     * @param string     $table
     * @param array      $data
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
     *
     * @param string     $table
     * @param array      $data
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
     * @param array  $data
     *
     * @throws Exception
     *
     * @return array
     */
    private function filter($table, $data)
    {
        $columns = $this->mapping->getTableColumns($table);
        $readOnly = $this->getReadOnlyColumns($table);

        $result = [];
        foreach ($columns as $column) {
            if ($this->mapping->isIdentifierColumn($table, $column->getName())) {
                continue;
            }

            if (!\array_key_exists($column->getName(), $data)) {
                continue;
            }

            if (\in_array($column->getName(), $readOnly, true)) {
                continue;
            }

            $value = $data[$column->getName()];

            if ($this->isDateColumn($column) && !$this->isValidDate($value)) {
                $result[$column->getName()] = null;
            } else {
                $result[$column->getName()] = $value;
            }
        }

        return $result;
    }

    private function isValidDate($date)
    {
        if (!$date) {
            return false;
        }
        if (strpos('0000-00-00', $date) !== false) {
            return false;
        }

        try {
            new DateTime($date);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function isDateColumn(Column $column)
    {
        return $column->getType() instanceof DateStringType || $column->getType() instanceof DateTimeStringType;
    }

    /**
     * @return string[]
     */
    private function getReadOnlyColumns(string $table): array
    {
        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select(['config.column_name'])
            ->from('s_attribute_configuration', 'config')
            ->where('config.table_name = :tablename')
            ->andWhere('config.readonly = 1')
            ->setParameter(':tablename', $table)
        ;

        return $builder->execute()->fetchAll(PDO::FETCH_COLUMN);
    }
}
