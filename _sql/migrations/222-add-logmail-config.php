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

class Migrations_Migration222 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<EOD
SET @plugin_id = (SELECT id FROM s_core_plugins WHERE name='ErrorHandler');

SET @parent_form_id = (SELECT id FROM  `s_core_config_forms` WHERE `name` LIKE "Core");
INSERT IGNORE INTO `s_core_config_forms` (`parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES (@parent_form_id, 'Log', 'Log', NULL, '0', '0', @plugin_id);

SET @form_id = (SELECT id FROM  `s_core_config_forms` WHERE plugin_id = @plugin_id);
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES (@form_id, 'logMail', 'i:0;', 'Fehler an Shopbetreiber senden', NULL, 'checkbox', '0', '0', '0', NULL, NULL, 'a:0:{}');
EOD;

        $this->addSql($sql);
    }
}
