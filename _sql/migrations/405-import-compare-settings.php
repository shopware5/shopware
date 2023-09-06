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

class Migrations_Migration405 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        /**
         * Get formId
         * Set plugin Id to NULL to be independent
         * Rename setting to be unique
         *
         * Get compare pluginId
         * remove compare plugin
         * remove subscribes of compare plugin
         */
        $sql = <<<'EOD'
            SET @configFormId = (SELECT id FROM s_core_config_forms WHERE name = 'Compare' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            UPDATE s_core_config_forms SET plugin_id = NULL WHERE id = @configFormId;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            UPDATE s_core_config_elements SET name = 'compareShow' WHERE form_id = @configFormId AND name = 'show';
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            SET @comparePluginId = (SELECT id FROM s_core_plugins WHERE name = 'Compare');
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            DELETE FROM s_core_plugins WHERE id = @comparePluginId;
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            DELETE FROM s_core_subscribes WHERE pluginID = @comparePluginId;
EOD;
        $this->addSql($sql);
    }
}
