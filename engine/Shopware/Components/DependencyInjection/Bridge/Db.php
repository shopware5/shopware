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

namespace Shopware\Components\DependencyInjection\Bridge;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class Db
{
    /**
     * @return \PDO
     */
    public static function createPDO(array $dbConfig)
    {
        if (isset($dbConfig['factory']) && class_exists($dbConfig['factory'])) {
            $factory = $dbConfig['factory'];

            return $factory::createPDO($dbConfig);
        }

        $password = $dbConfig['password'] ?? '';
        $connectionString = self::buildConnectionString($dbConfig);

        try {
            $conn = new \PDO(
                'mysql:' . $connectionString,
                $dbConfig['username'],
                $password,
                $dbConfig['pdoOptions']
            );

            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

            // Reset sql_mode "STRICT_TRANS_TABLES" that will be default in MySQL 5.6
            $conn->exec('SET @@session.sql_mode = ""');

            if (isset($dbConfig['timezone'])) {
                $conn->exec(sprintf('SET @@session.time_zone = %s;', $conn->quote($dbConfig['timezone'])));
            }
        } catch (\PDOException $e) {
            $message = str_replace(
                [
                    $dbConfig['username'],
                    $dbConfig['password'],
                ],
                '******',
                $e->getMessage()
            );

            throw new \RuntimeException(sprintf('Could not connect to database. Message from SQL Server: %s', $message));
        }

        return $conn;
    }

    /**
     * @param \PDO $pdo
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return Connection
     */
    public static function createDbalConnection(
        array $options,
        Configuration $config,
        EventManager $eventManager,
        $pdo
    ) {
        $options['pdo'] = $pdo;

        $options['driver'] = $options['adapter'];
        $options['user'] = $options['username'];

        unset($options['username'], $options['adapter']);

        return DriverManager::getConnection($options, $config, $eventManager);
    }

    /**
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public static function createEnlightDbAdapter(Connection $connection, array $options)
    {
        $options = ['dbname' => $options['dbname'], 'username' => null, 'password' => null];

        $db = \Enlight_Components_Db_Adapter_Pdo_Mysql::createFromDbalConnectionAndConfig($connection, $options);

        \Zend_Db_Table_Abstract::setDefaultAdapter($db);

        return $db;
    }

    private static function buildConnectionString(array $dbConfig): string
    {
        if (!isset($dbConfig['host']) || empty($dbConfig['host'])) {
            $dbConfig['host'] = 'localhost';
        }

        $connectionSettings = [
            'host=' . $dbConfig['host'],
        ];

        if (!empty($dbConfig['socket'])) {
            $connectionSettings[] = 'unix_socket=' . $dbConfig['socket'];
        }

        if (!empty($dbConfig['port'])) {
            $connectionSettings[] = 'port=' . $dbConfig['port'];
        }

        if (!empty($dbConfig['charset'])) {
            $connectionSettings[] = 'charset=' . $dbConfig['charset'];
        }

        if (!empty($dbConfig['dbname'])) {
            $connectionSettings[] = 'dbname=' . $dbConfig['dbname'];
        }

        return implode(';', $connectionSettings);
    }
}
