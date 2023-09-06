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

class Migrations_Migration352 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
CREATE TABLE IF NOT EXISTS `s_core_templates_config_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `default_value` text COLLATE utf8_unicode_ci,
  `selection` text COLLATE utf8_unicode_ci,
  `field_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `support_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `allow_blank` int(1) NOT NULL DEFAULT '1',
  `container_id` int(11) NOT NULL,
  `attributes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_id_name` (`template_id`, `name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'

CREATE TABLE IF NOT EXISTS `s_core_templates_config_layout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `template_id` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'

CREATE TABLE IF NOT EXISTS `s_core_templates_config_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `element_id_shop_id` (`element_id`,`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'

CREATE TABLE IF NOT EXISTS `s_core_templates_config_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `element_values` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'

CREATE TABLE IF NOT EXISTS `s_core_theme_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compiler_force` int(1) NOT NULL,
  `compiler_create_source_map` int(1) NOT NULL,
  `compiler_compress_css` int(1) NOT NULL,
  `compiler_compress_js` int(1) NOT NULL,
  `force_reload_snippets` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT INTO `s_core_theme_settings`
(`compiler_force`, `compiler_create_source_map`, `compiler_compress_css`, `compiler_compress_js`)
VALUES
(0, 0, 1, 1);
EOD;
        $this->addSql($sql);
    }
}
