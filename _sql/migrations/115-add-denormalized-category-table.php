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

class Migrations_Migration115 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
CREATE TABLE IF NOT EXISTS `s_articles_categories_ro` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `categoryID` int(11) unsigned NOT NULL,
  `parentCategoryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`categoryID`,`parentCategoryID`),
  KEY `categoryID` (`categoryID`),
  KEY `articleID_2` (`articleID`),
  KEY `categoryID_2` (`categoryID`,`parentCategoryID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOD;

        $this->addSql($sql);
    }
}
