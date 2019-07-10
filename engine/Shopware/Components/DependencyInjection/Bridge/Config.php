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

use Doctrine\DBAL\Connection;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware_Components_Config;
use Zend_Cache_Core;

class Config
{
    /**
     * @param Connection $db
     * @param array      $config
     *
     * @return Shopware_Components_Config|null
     */
    public function factory(
        Zend_Cache_Core $cache,
        Connection $db = null,
        $config = [],
        ShopwareReleaseStruct $release
    ) {
        if (!$db) {
            return null;
        }

        if (!isset($config['cache'])) {
            $config['cache'] = $cache;
        }
        $config['db'] = $db;
        $config['release'] = $release;

        return new Shopware_Components_Config($config);
    }
}
