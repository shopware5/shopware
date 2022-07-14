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

class Migrations_Migration223 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Statistics' LIMIT 1);

        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
        (NULL, @parent, 'maximumReferrerAge', 's:2:"90";', 'Maximales Alter für Referrer Statistikdaten', 'Alte Referrer Daten werden über den Aufräumen Cronjob gelöscht, falls aktiv', 'text', 0, 0, 1, NULL, NULL, 'a:0:{}');

        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'maximumReferrerAge' LIMIT 1);
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Maximum age for referrer statistics', 'Old referrer data will be deleted by the cron job call if active' );


        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
        (NULL, @parent, 'maximumImpressionAge', 's:2:"90";', 'Maximales Alter für Artikel-Impressions', 'Alte Impression Daten werden über den Aufräumen Cronjob gelöscht, falls aktiv', 'text', 0, 0, 1, NULL, NULL, 'a:0:{}');

        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'maximumImpressionAge' LIMIT 1);
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Maximum age for impression statistics', 'Old impression data will be deleted by the cron job call if active' );
EOD;
        $this->addSql($sql);
    }
}
