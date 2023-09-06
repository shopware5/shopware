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

class Migrations_Migration422 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = '
            CREATE TABLE IF NOT EXISTS `s_core_plugin_categories` (
                `id` int(11) NOT NULL,
                `locale` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                `parent_id` int(11) NULL,
                `name` text COLLATE utf8_unicode_ci NOT NULL,
            PRIMARY KEY (`id`,`locale`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ';
        $this->addSql($sql);

        $sql = "
            INSERT IGNORE INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `author`, `copyright`, `license`, `version`, `support`, `changes`, `link`, `store_version`, `store_date`, `capability_update`, `capability_install`, `capability_enable`,  `update_source`, `update_version`, `capability_secure_uninstall`) VALUES
            (NULL, 'Backend', 'PluginManager', 'Plugin Manager', 'Default', NULL, NULL, 1, '2014-11-07 11:55:46', '2014-11-07 11:55:54', '2014-11-07 11:55:54', '2014-11-07 11:55:57', 'shopware AG', 'Copyright © 2012, shopware AG', NULL, '1.0.0', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, NULL, 0);
        ";

        $this->addSql($sql);

        $this->addSql("SET @pluginId = (SELECT id FROM s_core_plugins WHERE `name`= 'PluginManager' LIMIT 1);");

        $this->addSql("
            INSERT IGNORE INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
            (NULL, 'Enlight_Controller_Dispatcher_ControllerPath_Backend_PluginManager', 0, 'Shopware_Plugins_Backend_PluginManager_Bootstrap::getDefaultControllerPath', @pluginId, 0),
            (NULL, 'Enlight_Controller_Dispatcher_ControllerPath_Backend_PluginInstaller', 0, 'Shopware_Plugins_Backend_PluginManager_Bootstrap::getDefaultControllerPath', @pluginId, 0);
        ");

        $this->addSql("
            INSERT IGNORE INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `pluginID`, `resourceID`, `controller`, `shortcut`, `action`) VALUES
            (NULL, 23, '', 'Plugin Manager', NULL, NULL, 'sprite-application-block', 0, 1, @pluginId, NULL, 'PluginManager', NULL, 'Index');
        ");
    }
}
