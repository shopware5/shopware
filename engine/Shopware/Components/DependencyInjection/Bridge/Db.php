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

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Db
{
    /**
     * @param array $options
     * @param Configuration $config
     * @param EventManager $eventManager
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function createDbalConnection(
        array $options,
        Configuration $config,
        EventManager $eventManager
    ) {
        $connectionParams = array(
            'dbname' => $options['dbname'],
            'user' => $options['username'],
            'password' => $options['password'],
            'host' => $options['host'],
            'driver' => $options['adapter'],
            'charset' => $options['charset'],
            'wrapperClass' => isset($options['wrapperClass']) ? $options['wrapperClass'] : null,
        );

        $connectionParams['driverOptions'] = [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET @@session.sql_mode = '';",  // Reset sql_mode "STRICT_TRANS_TABLES" that will be default in MySQL 5.6
        ];

        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config, $eventManager);

        return $conn;
    }

    /**
     * @param Connection $connection
     * @param array $options
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public static function createEnlightDbAdapter(Connection $connection, array $options)
    {
        $db = \Enlight_Components_Db_Adapter_Pdo_Mysql::createFromDbalConnectionAndConfig($connection, $options);

        \Zend_Db_Table_Abstract::setDefaultAdapter($db);

        return $db;
    }
}
