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

class TableMapping
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $tables;

    public function __construct(Connection $connection, array $tableMapping)
    {
        $this->connection = $connection;
        $this->tables = $tableMapping;
    }

    /**
     * @param string $table
     * @param string $name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function isIdentifierColumn($table, $name)
    {
        if (!array_key_exists($table, $this->tables)) {
            throw new \Exception(sprintf('Table %s is no attribute table', $table));
        }
        $config = $this->tables[$table];
        $identifiers = isset($config['identifiers']) ? $config['identifiers'] : [];
        $columns = array_map('strtolower', $identifiers);

        return in_array(strtolower($name), $columns);
    }

    /**
     * @param string $table
     * @param string $name
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function isCoreColumn($table, $name)
    {
        if (!array_key_exists($table, $this->tables)) {
            throw new \Exception(sprintf('Table %s is no attribute table', $table));
        }
        $config = $this->tables[$table];
        $coreAttributes = isset($config['coreAttributes']) ? $config['coreAttributes'] : [];
        $columns = array_map('strtolower', $coreAttributes);

        return in_array(strtolower($name), $columns);
    }

    /**
     * @param string $table
     *
     * @return string|null
     */
    public function getTableModel($table)
    {
        if (!array_key_exists($table, $this->tables)) {
            return null;
        }

        return $this->tables[$table]['model'];
    }

    /**
     * @return array
     */
    public function getAttributeTables()
    {
        return array_filter($this->tables, function ($table) {
            return !$table['readOnly'];
        });
    }

    /**
     * @param string $table
     *
     * @return string
     */
    public function getTableForeignKey($table)
    {
        return $this->tables[$table]['foreignKey'];
    }

    /**
     * @param string $table
     *
     * @return bool
     */
    public function isAttributeTable($table)
    {
        return array_key_exists($table, $this->tables);
    }

    /**
     * @param string $table
     * @param string $column
     *
     * @return bool
     */
    public function isTableColumn($table, $column)
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns($table);
        $names = array_map(function (Column $schemaColumn) {
            return strtolower($schemaColumn->getName());
        }, $columns);

        return in_array(strtolower($column), $names);
    }

    /**
     * @param string $table
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getDependingTables($table)
    {
        if (!$this->isAttributeTable($table)) {
            throw new \Exception(sprintf('Table %s is no supported attribute table', $table));
        }

        return $this->tables[$table]['dependingTables'];
    }

    /**
     * @param string $table
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getTableColumns($table)
    {
        return $this->connection->getSchemaManager()->listTableColumns($table);
    }
}
