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

class Migrations_Migration950 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->createPresetsTable();
        $this->createPresetsTranslationTable();
    }

    private function createPresetsTable()
    {
        $sql = <<<'EOD'
CREATE TABLE IF NOT EXISTS `s_emotion_presets` (
  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `premium` TINYINT(1) NOT NULL DEFAULT '0',
  `custom` TINYINT(1) NOT NULL DEFAULT '1',
  `thumbnail` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
  `preview` TEXT COLLATE utf8_unicode_ci DEFAULT NULL,
  `preset_data` LONGTEXT COLLATE utf8_unicode_ci NOT NULL,
  `required_plugins` LONGTEXT COLLATE utf8_unicode_ci DEFAULT NULL,
  `assets_imported` TINYINT(1) NOT NULL DEFAULT '1',
  UNIQUE KEY `name` (`name`),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOD;
        $this->addSql($sql);
    }

    private function createPresetsTranslationTable()
    {
        $sql = <<<'EOD'
CREATE TABLE IF NOT EXISTS `s_emotion_preset_translations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `presetID` int(11) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'de_DE',
  PRIMARY KEY (`id`),
  UNIQUE KEY `presetID` (`presetID`,`locale`),
  CONSTRAINT `s_emotion_preset_translations_preset_fk` FOREIGN KEY (`presetID`) REFERENCES `s_emotion_presets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
EOD;
        $this->addSql($sql);
    }
}
