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

class Migrations_Migration441 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_core_config_forms` WHERE `name`='Esd');
INSERT IGNORE INTO `s_core_config_elements`
(`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
VALUES (NULL, @parent, 'showEsd', 'b:1;', 'Sofortdownloads im Account anzeigen', 'Sofortdownloads können weiterhin über die Bestellübersicht heruntergeladen werden.', 'boolean', '1', '5', '1', NULL, NULL, NULL);

SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showEsd' LIMIT 1);
SET @localeID = (SELECT id FROM s_core_locales WHERE locale='en_GB');
INSERT IGNORE INTO s_core_config_element_translations
(element_id, locale_id, label, description)
VALUES (@elementID, @localeID, 'Show instant downloads in account', 'Instant downloads can already be downloaded from the order details page.');
EOD;

        $this->addSql($sql);
    }
}
