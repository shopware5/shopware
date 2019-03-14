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

namespace Shopware\Components\Install;

class Database
{
    /**
     * @var \PDO
     */
    protected $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $dbName
     * @param string $file
     *
     * @throws \Exception
     */
    public function importFile($dbName, $file)
    {
        $this->connection->query(sprintf('use `%s`', $dbName));

        if (($contents = file_get_contents($file)) === false) {
            throw new \Exception(sprintf('Could not open file: %s', $file));
        }

        $rows = explode(";\n", trim($contents));
        foreach ($rows as $row) {
            $this->connection->exec(trim($row));
        }
    }

    /**
     * @param string $url
     * @param string $dbName
     *
     * @throws \InvalidArgumentException
     */
    public function setupShop($url, $dbName)
    {
        $parts = parse_url($url);
        if ($parts === false || !array_key_exists('host', $parts)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid Shop URL (%s)', $url)
            );
        }

        $isSecure = $parts['scheme'] === 'https';
        $host = $parts['host'];
        $path = array_key_exists('path', $parts) ? $parts['path'] : '';
        $host .= array_key_exists('port', $parts) ? ':' . $parts['port'] : '';

        if ($path === '/') {
            $path = '';
        }

        if (!empty($path)) {
            $path = trim($path, '/');
            $path = '/' . $path;
        }

        $this->connection->query(sprintf('use `%s`', $dbName));

        $stmt = $this->connection->prepare('UPDATE `s_core_shops` SET `host` = :host, `base_path` = :path, `secure` = :isSecure WHERE `main_id` IS NULL');
        $stmt->execute([
            'host' => $host,
            'path' => $path,
            'isSecure' => $isSecure,
        ]);
    }

    /**
     * @param string $dbName
     */
    public function emptyDatabase($dbName)
    {
        $this->connection->query(sprintf('use `%s`', $dbName));

        $sql = <<<'SQL'
SET FOREIGN_KEY_CHECKS = 0;
SET GROUP_CONCAT_MAX_LEN=32768;
SET @tables = NULL;
SELECT GROUP_CONCAT('`', table_name, '`') INTO @tables
  FROM information_schema.tables
  WHERE table_schema = (SELECT DATABASE());
SELECT IFNULL(@tables,'dummy') INTO @tables;

SET @tables = CONCAT('DROP TABLE IF EXISTS ', @tables);
PREPARE stmt FROM @tables;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
SET FOREIGN_KEY_CHECKS = 1;
SQL;

        $this->connection->exec($sql);
    }

    /**
     * create database if not exists
     *
     * @param string $dbName
     */
    public function dropDatabase($dbName)
    {
        $this->connection->exec(
            sprintf(
                'DROP DATABASE IF EXISTS `%s`',
                $dbName
            )
        );
    }

    /**
     * create database if not exists
     *
     * @param string $dbName
     */
    public function createDatabase($dbName)
    {
        $this->connection->exec(
            sprintf(
                'CREATE DATABASE `%s`',
                $dbName
            )
        );

        $this->connection->exec(
            sprintf(
                'ALTER DATABASE `%s` DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;',
                $dbName
            )
        );
    }
}
