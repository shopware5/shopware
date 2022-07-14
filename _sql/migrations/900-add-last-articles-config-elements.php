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

class Migrations_Migration900 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->removeLastArticlesPlugin();
        $this->cleanupLastArticlesTable();
        $this->renameConfigElementKeys();
    }

    private function cleanupLastArticlesTable()
    {
        $this->addSql('ALTER TABLE `s_emarketing_lastarticles` DROP `img`, DROP `name`;');
    }

    private function removeLastArticlesPlugin()
    {
        $this->addSql('DELETE FROM s_core_plugins WHERE `name` = "LastArticles"');
        $this->addSql('UPDATE s_core_config_forms SET plugin_id = NULL WHERE name = "LastArticles";');
    }

    private function renameConfigElementKeys()
    {
        $this->addSql("SET @formId = (SELECT id FROM `s_core_config_forms` WHERE `name` = 'LastArticles' LIMIT 1);");
        $this->addSql("UPDATE s_core_config_elements SET `name` = 'lastarticles_show' WHERE form_id = @formId AND `name` = 'show'");
        $this->addSql("UPDATE s_core_config_elements SET `name` = 'lastarticles_controller' WHERE form_id = @formId AND `name` = 'controller'");
        $this->addSql("UPDATE s_core_config_elements SET `name` = 'lastarticles_time' WHERE form_id = @formId AND `name` = 'time'");
    }
}
