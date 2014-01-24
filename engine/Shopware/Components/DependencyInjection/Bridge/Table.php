<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
class Table
{
    /**
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql  $db
     * @param \Zend_Cache_Core                          $cache
     * @return bool
     */
    public function factory(\Enlight_Components_Db_Adapter_Pdo_Mysql $db, \Zend_Cache_Core $cache)
    {
        \Zend_Db_Table_Abstract::setDefaultAdapter($db);
        \Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

        return true;
    }
}
