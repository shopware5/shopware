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

class Migrations_Migration814 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `s_article_configurator_options_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `optionID` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `optionID` (`optionID`),
  CONSTRAINT `s_article_configurator_options_attributes_ibfk_1` FOREIGN KEY (`optionID`) REFERENCES `s_article_configurator_options` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `s_article_configurator_groups_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  CONSTRAINT `s_article_configurator_groups_attributes_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `s_article_configurator_groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SQL;
        $this->addSql($sql);
    }
}
