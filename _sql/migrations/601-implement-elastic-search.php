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

class Migrations_Migration601 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
CREATE TABLE IF NOT EXISTS `s_es_backlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

EOD;
        $this->addSql($sql);

        $sql = '
            ALTER TABLE s_articles_categories_ro
            ADD INDEX `elastic_search` (`categoryID`,`articleID`);
        ';
        $this->addSql($sql);

        $sql = "
            INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`)
            VALUES (NULL, '0', 'lastBacklogId', 'i:0;', '', 'Last processed backlog id', '', '0', '0', '0', NULL, NULL)
        ";
        $this->addSql($sql);

        $sql = "
            SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Search' LIMIT 1);
        ";
        $this->addSql($sql);

        $sql = "
INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @formId, 'activateNumberSearch', 'i:1;', 'Nummern Suche aktivieren', NULL, 'checkbox', 1, 0, 0, NULL, NULL, NULL);
        ";
        $this->addSql($sql);
    }
}
