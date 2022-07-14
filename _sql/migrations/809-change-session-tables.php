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

class Migrations_Migration809 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql(<<<SQL
DROP TABLE `s_core_sessions`;
SQL
        );

        $this->addSql(<<<SQL
CREATE TABLE `s_core_sessions` (
    `id` VARCHAR(128) NOT NULL PRIMARY KEY,
    `data` MEDIUMBLOB NOT NULL,
    `modified` INTEGER UNSIGNED NOT NULL,
    `expiry` MEDIUMINT NOT NULL
) COLLATE utf8_bin, ENGINE = InnoDB;
SQL
        );

        $this->addSql(<<<SQL
DROP TABLE `s_core_sessions_backend`;
SQL
        );

        $this->addSql(<<<SQL
CREATE TABLE `s_core_sessions_backend` (
    `id` VARCHAR(128) NOT NULL PRIMARY KEY,
    `data` MEDIUMBLOB NOT NULL,
    `modified` INTEGER UNSIGNED NOT NULL,
    `expiry` MEDIUMINT NOT NULL
) COLLATE utf8_bin, ENGINE = InnoDB;
SQL
        );
    }
}
