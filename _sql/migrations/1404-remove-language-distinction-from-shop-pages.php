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

class Migrations_Migration1404 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $dropEnglish = <<<'SQL'
DELETE FROM `s_cms_static_groups`
WHERE `key` LIKE 'e%';

DELETE FROM `s_cms_static`
WHERE `grouping` LIKE 'e%';
SQL;

        $renameGerman = <<<'SQL'
UPDATE `s_cms_static_groups`
SET `key` = LOWER(SUBSTR(`key`, 2));

UPDATE `s_cms_static`
SET `grouping` = REPLACE(REPLACE(`grouping`, 'gLeft', 'left'), 'gBottom', 'bottom');
SQL;

        if ($modus === self::MODUS_INSTALL) {
            $this->addSql($dropEnglish);
            $this->addSql($renameGerman);
        }
    }
}
