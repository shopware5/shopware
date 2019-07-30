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
class Migrations_Migration1605 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('CREATE TABLE `s_plugin_schema_version` (
    `plugin_name` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
    `version` INT(11) NOT NULL,
    `start_date` DATETIME NOT NULL,
    `complete_date` DATETIME NULL DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL COLLATE \'utf8_unicode_ci\',
    `error_msg` VARCHAR(255) NULL DEFAULT NULL COLLATE \'utf8_unicode_ci\',
    PRIMARY KEY (`plugin_name`, `version`)
)
COLLATE=\'utf8_unicode_ci\'
ENGINE=InnoDB
');
    }
}
