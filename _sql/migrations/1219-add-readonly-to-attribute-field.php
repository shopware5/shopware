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

class Migrations_Migration1219 extends Shopware\Components\Migrations\AbstractMigration {
    public function up($modus) {
        $this->addSQL('ALTER TABLE `s_attribute_configuration` ADD `readonly` TINYINT NOT NULL DEFAULT 0 AFTER `label`;');

        $sql = <<<EOT
SET @locale_de_DE = (SELECT id FROM s_core_locales WHERE locale = 'de_DE');
SET @locale_en_GB = (SELECT id FROM s_core_locales WHERE locale = 'en_GB');
INSERT IGNORE INTO s_core_snippets (namespace, shopID, localeID, name, value, created, updated, dirty) VALUES
('backend/attributes/main', 1, @locale_en_GB, 'readonly', 'Readonly in backend', '2018-04-24 09:43:14', '2018-04-24 09:43:14', 0),
('backend/attributes/main', 1, @locale_de_DE, 'readonly', 'Schreibgeschützt im Backend', '2018-04-24 09:43:14', '2018-04-24 09:43:14', 0);
EOT;

        $this->addSql($sql);
    }
}
