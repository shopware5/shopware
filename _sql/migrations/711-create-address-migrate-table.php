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

class Migrations_Migration711 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     *
     * @return void
     */
    public function up($modus)
    {
        if ($modus == self::MODUS_INSTALL) {
            return;
        }

        $this->createMigrationFields();

        $sql = <<<SQL
CREATE TABLE `s_user_addresses_migration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` int(11) NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `ustid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `checksum` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `original_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `original_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE `unik` (`checksum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;

        $this->addSql($sql);
    }

    private function createMigrationFields()
    {
        $sql = <<<SQL
ALTER TABLE `s_user_addresses`
  ADD `migration_id` int(11) DEFAULT NULL,
  ADD INDEX `migrate` (`migration_id`);
SQL;

        $this->addSql($sql);
    }
}
