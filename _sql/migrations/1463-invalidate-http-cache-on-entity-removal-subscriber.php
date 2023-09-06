<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

class Migrations_Migration1463 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @plugin_id = (SELECT id FROM s_core_plugins WHERE name='HttpCache');
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Article\\Price::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Article\\Article::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Article\\Detail::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Category\\Category::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Banner\\Banner::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Blog\\Blog::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Emotion\\Emotion::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Site\\Site::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
EOD;
        $this->addSql($sql);
    }
}
