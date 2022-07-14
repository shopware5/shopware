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

class Migrations_Migration1701 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Update user entries
        $values = $this->connection->query('SELECT v.id, v.value FROM s_core_config_values v INNER JOIN s_core_config_elements e ON e.id = v.element_id WHERE e.name = \'cacheControllers\'')
            ->fetchAll(PDO::FETCH_ASSOC);

        foreach ($values as $row) {
            $value = $this->addCacheEntry($row['value']);

            $sql = sprintf('UPDATE `s_core_config_values` SET `value` = \'%s\' WHERE id = %d', $value, (int) $row['id']);
            $this->addSql($sql);
        }

        // Update default
        $value = $this->connection->query('SELECT `value` FROM s_core_config_elements WHERE `name` = "cacheControllers"')->fetchColumn();
        $value = $this->addCacheEntry($value);
        $this->addSql(sprintf('UPDATE s_core_config_elements SET `value` = \'%s\' WHERE `name` = "cacheControllers"', $value));

        $sql = <<<'EOD'
SET @plugin_id = (SELECT id FROM s_core_plugins WHERE name='HttpCache');
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Form\\Form::postPersist', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Form\\Form::postUpdate', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Form\\Form::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Form\\Field::postPersist', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Form\\Field::postUpdate', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES ('Shopware\\Models\\Form\\Field::postRemove', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @plugin_id, 0);
EOD;
        $this->addSql($sql);
    }

    private function addCacheEntry(string $entry): string
    {
        $value = unserialize($entry, ['allowed_classes' => false]);
        $value = explode("\n", $value);
        $value[] = 'frontend/forms 14400';
        $value = implode("\n", $value);

        return serialize($value);
    }
}
