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

class Migrations_Migration924 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("SET @pluginId = (SELECT id FROM s_core_plugins WHERE name = 'HttpCache')");

        $this->addSql(
"INSERT INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`)
VALUES('Enlight_Bootstrap_InitResource_http_cache.cache_control', '0', 'Shopware_Plugins_Core_HttpCache_Bootstrap::initCacheControl', @pluginId, '0');"
        );

        $this->addSql(
"INSERT INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`)
VALUES('Enlight_Bootstrap_InitResource_http_cache.cache_id_collector', '0', 'Shopware_Plugins_Core_HttpCache_Bootstrap::initCacheIdCollector', @pluginId, '0');"
        );
    }
}
