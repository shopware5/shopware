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

namespace Shopware\Recovery\Install\Service;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Install\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class DatabaseService
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $databaseName
     */
    public function createDatabase($databaseName)
    {
        $this->connection->exec(
            sprintf(
                "CREATE DATABASE %s CHARACTER SET utf8 COLLATE utf8_unicode_ci;",
                $databaseName
            )
        );
    }

    /**
     * @return int
     */
    public function getTableCount()
    {
        $tables = $this->connection->query("SHOW TABLES")->fetchAll();

        return count($tables);
    }

    /**
     * @return string[]
     */
    public function getAvailableDatabaseNames()
    {
        $stmt = $this->connection->query("SHOW DATABASES");
        $ignore = ['information_schema', 'performance_schema'];
        $names = [];
        while ($name = $stmt->fetchColumn(0)) {
            if (in_array($name, $ignore)) {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }

    /**
     * @return bool
     */
    public function containsShopwareSchema()
    {
        try {
            $this->connection->query('SELECT * FROM s_schema_version')->fetchAll();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
