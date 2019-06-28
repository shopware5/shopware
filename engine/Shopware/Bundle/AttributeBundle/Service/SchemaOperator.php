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

class SchemaOperator
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TableMapping
     */
    private $tableMapping;

    /**
     * @var array
     */
    private $nameBlacklist;

    public function __construct(Connection $connection, TableMapping $tableMapping)
    {
        $this->connection = $connection;
        $this->tableMapping = $tableMapping;
        $this->nameBlacklist = include __DIR__ . '/../DependencyInjection/Resources/column_name_blacklist.php';
    }

    /**
     * @param string                $table
     * @param string                $column
     * @param string                $type
     * @param string|int|float|null $defaultValue
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function createColumn($table, $column, $type, $defaultValue = null)
    {
        $this->validate($table, $column);
        $defaultValue = $this->filterDefaultValue($defaultValue);

        if (!$type) {
            throw new \Exception('No column type provided');
        }

        $sql = sprintf('ALTER TABLE `%s` ADD `%s` %s NULL DEFAULT %s',
            $table,
            $column,
            $type,
            $defaultValue
        );

        $this->connection->executeQuery($sql);
    }

    /**
     * @param string                $table
     * @param string                $originalName
     * @param string                $newName
     * @param string                $type
     * @param string|int|float|null $defaultValue
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function changeColumn($table, $originalName, $newName, $type, $defaultValue = null)
    {
        $this->validate($table, $originalName);

        if (!$newName) {
            throw new \Exception('No column name provided');
        }
        if (!$type) {
            throw new \Exception('No column type provided');
        }

        $this->validateField($newName);
        $defaultValue = $this->filterDefaultValue($defaultValue);

        $sql = sprintf('ALTER TABLE `%s` CHANGE `%s` `%s` %s NULL DEFAULT %s;',
            $table,
            $originalName,
            $newName,
            $type,
            $defaultValue
        );

        $this->connection->executeQuery($sql);
    }

    /**
     * @param string $table
     * @param string $column
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function dropColumn($table, $column)
    {
        $this->validate($table, $column);

        if ($this->tableMapping->isCoreColumn($table, $column)) {
            throw new \Exception(sprintf('Provided column is an core attribute column: %s', $column));
        }

        $sql = sprintf('ALTER TABLE `%s` DROP `%s`', $table, $column);
        $this->connection->executeQuery($sql);
    }

    /**
     * Updates the provided column data to sql NULL value
     *
     * @param string $table
     * @param string $column
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function resetColumn($table, $column)
    {
        $this->validate($table, $column);

        if (!$this->tableMapping->isTableColumn($table, $column)) {
            throw new \Exception(sprintf('Provided column %s does not exist in table %s', $column, $table));
        }

        $sql = sprintf('UPDATE `%s` SET `%s` = NULL', $table, $column);
        $this->connection->executeUpdate($sql);
    }

    /**
     * @param string $table
     * @param string $name
     *
     * @throws \Exception
     */
    private function validate($table, $name)
    {
        if (!$table) {
            throw new \Exception('No table name provided');
        }
        if (!$name) {
            throw new \Exception('No column name provided');
        }

        $this->validateField($table);
        $this->validateField($name);

        if (!$this->tableMapping->isAttributeTable($table)) {
            throw new \Exception(sprintf('Provided table is no attribute table: %s', $table));
        }
        if ($this->tableMapping->isIdentifierColumn($table, $name)) {
            throw new \Exception(sprintf('Provided column is an identifier column: %s', $name));
        }

        $lowerCaseName = strtolower($name);
        if (in_array($lowerCaseName, $this->nameBlacklist)) {
            throw new \Exception(sprintf('Provided name %s is a reserved keyword.', $name));
        }
    }

    /**
     * @param string $field
     *
     * @throws \Exception
     */
    private function validateField($field)
    {
        if (strlen($field) > 64) {
            throw new \Exception('Maximum length: 64 chars');
        }

        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field)) {
            throw new \Exception(sprintf('Invalid chars in %s', $field));
        }
    }

    /**
     * @param float|int|string|null $defaultValue
     *
     * @return float|int|string|null
     */
    private function filterDefaultValue($defaultValue)
    {
        if (!is_string($defaultValue)) {
            return $defaultValue;
        }

        if ($defaultValue === 'NULL') {
            return $defaultValue;
        }

        return $this->connection->quote(
            str_replace(['`', '\''], '', $defaultValue)
        );
    }
}
