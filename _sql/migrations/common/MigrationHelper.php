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

class MigrationHelper
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $table
     *
     * @return array<array{name: string, type: string}>
     */
    public function getList($table)
    {
        $identifiers = ['id', 'billingID', 'shippingID'];

        $columns = $this->connection->query(sprintf('DESCRIBE `%s`', $table))->fetchAll(PDO::FETCH_ASSOC);

        $definition = [];

        foreach ($columns as $column) {
            if (\in_array($column['Field'], $identifiers)) {
                continue;
            }
            $definition[] = ['name' => $column['Field'], 'type' => $column['Type']];
        }

        return $definition;
    }

    /**
     * @param string $table
     * @param string $name
     * @param string $type
     *
     * @return void
     */
    public function update($table, $name, $type)
    {
        if ($this->get($table, $name) !== null) {
            $this->changeColumn($table, $name, $type);

            return;
        }

        $this->createColumn($table, $name, $type);
    }

    /**
     * @param string $table
     * @param string $keyColumn
     *
     * @return void
     */
    public function migrateAttributes($table, $keyColumn)
    {
        $attributes = $this->getList($table);

        if (empty($attributes)) {
            return;
        }

        $names = array_column($attributes, 'name');

        $prefixed = array_map(function ($name) {
            return 'attr.' . $name;
        }, $names);

        $names = implode(',', $names);
        $prefixed = implode(',', $prefixed);

        $type = str_replace('_attributes', '', $table);

        $sql = <<<SQL
          INSERT IGNORE INTO s_user_addresses_attributes (address_id, $names)
          SELECT
              address.id as address_id,
              $prefixed
          FROM s_user_addresses address
            INNER JOIN $table as attr
              ON address.original_id = attr.$keyColumn
              AND address.original_type = '$type'
SQL;

        $this->connection->exec($sql);
    }

    private function createColumn(string $table, string $name, string $type): void
    {
        $sql = sprintf('ALTER TABLE `%s` ADD `%s` %s NULL DEFAULT NULL', $table, $name, $type);
        $this->connection->exec($sql);
    }

    private function changeColumn(string $table, string $name, string $type): void
    {
        $sql = sprintf('ALTER TABLE `%s` CHANGE `%s` `%s` %s NULL DEFAULT NULL;', $table, $name, $name, $type);
        $this->connection->exec($sql);
    }

    /**
     * @return array{name: string, type: string}|null
     */
    private function get(string $table, string $name): ?array
    {
        foreach ($this->getList($table) as $column) {
            if ($name === $column['name']) {
                return $column;
            }
        }

        return null;
    }
}
