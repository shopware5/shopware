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

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Db
{
    /**
     * @param string $adapter
     * @param array $options
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function factory($adapter, $options)
    {
        /** @var \Enlight_Components_Db_Adapter_Pdo_Mysql $db */
        $db = \Enlight_Components_Db::factory($adapter, $options);

        // Reset sql_mode "STRICT_TRANS_TABLES" that will be default in MySQL 5.6
        $db->exec("SET @@session.sql_mode = ''");

        \Zend_Db_Table_Abstract::setDefaultAdapter($db);

        return $db;
    }
}
