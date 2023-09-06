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

class Migrations_Migration141 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
    SET @formId = (SELECT id FROM s_core_config_forms WHERE label = 'SEO/Router-Einstellungen' LIMIT 1);

    INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
    (NULL, @formId, 'forceCanonicalHttp', 'b:1;', 'Canonical immer mit HTTP', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL);

    SET @elementId = (SELECT id FROM s_core_config_elements WHERE name ='forceUnsecureCanonical' LIMIT 1);

    INSERT IGNORE INTO `s_core_config_element_translations` (`id` ,`element_id` ,`locale_id` ,`label` ,`description`)
    VALUES (NULL,  @elementId,  '2',  'Force http canonical url', NULL);
EOD;

        $this->addSql($sql);
    }
}
