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

/**
 * @category  Shopware
 * @package   Shopware\Bundle\AttributeBundle\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class TableMapping implements TableMappingInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $tables;

    /**
     * TableMapping constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->tables = include __DIR__ . '/../DependencyInjection/Resources/table_entity_mapping.php';
    }

    /**
     * @param string $table
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function isIdentifierColumn($table, $name)
    {
        if (!array_key_exists($table, $this->tables)) {
            throw new \Exception(sprintf("Table %s is no attribute table", $table));
        }
        $config = $this->tables[$table];
        $columns = array_map('strtolower', $config['identifiers']);
        return in_array(strtolower($name), $columns);
    }

    /**
     * @param string $table
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function isCoreColumn($table, $name)
    {
        if (!array_key_exists($table, $this->tables)) {
            throw new \Exception(sprintf("Table %s is no attribute table", $table));
        }
        $config = $this->tables[$table];
        $columns = array_map('strtolower', $config['coreAttributes']);
        return in_array(strtolower($name), $columns);
    }

    /**
     * @param $table
     * @return null|string
     */
    public function getTableModel($table)
    {
        if (!array_key_exists($table, $this->tables)) {
            return null;
        }

        return $this->tables[$table]['model'];
    }

    /**
     * @return string[]
     */
    public function getAttributeTables()
    {
        return array_filter($this->tables, function ($table) {
            return !$table['readOnly'];
        });
    }

    /**
     * @param $table
     * @return string
     */
    public function getTableForeignKey($table)
    {
        return $this->tables[$table]['foreignKey'];
    }

    /**
     * @param string $table
     * @return bool
     */
    public function isAttributeTable($table)
    {
        return array_key_exists($table, $this->tables);
    }

    /**
     * @param string $table
     * @param string $column
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
     * @return array
     * @throws \Exception
     */
    public function getDependingTables($table)
    {
        if (!$this->isAttributeTable($table)) {
            throw new \Exception(sprintf("Table %s is no supported attribute table"));
        }
        return $this->tables[$table]['dependingTables'];
    }

    /**
     * @param string $table
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getTableColumns($table)
    {
        return $this->connection->getSchemaManager()->listTableColumns($table);
    }
}
