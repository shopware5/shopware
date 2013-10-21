<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Shopware_Components_Config;
use Zend_Cache_Core;

/**
* @category  Shopware
* @package   Shopware\Components\DependencyInjection\Bridge
* @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Config
{
    private $cache;
    private $db;
    private $configOptions;

    public function __construct(
        Zend_Cache_Core $cache,
        $configOptions = array(),
        Enlight_Components_Db_Adapter_Pdo_Mysql $db = null
    ) {
        $this->cache = $cache;
        $this->configOptions = $configOptions;
        $this->db = $db;

    }

    public function factory()
    {
        if (!$this->db) {
            return null;
        }

        $configs = $this->configOptions;

        if (!isset($configs['cache'])) {
            $configs['cache'] = $this->cache;
        }
        $configs['db'] = $this->db;

        return new Shopware_Components_Config($configs);
    }
}
