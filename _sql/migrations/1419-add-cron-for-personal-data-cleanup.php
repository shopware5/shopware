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
class Migrations_Migration1419 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * We need to rerun the Migrations from 5.4.5 <-> 5.4.6, to make the 5.5 beta 1 updatable
     *
     * @param string $modus
     */
    public function up($modus)
    {
        if ($this->connection->query('SELECT 1 FROM s_schema_version WHERE version = 1219')->fetchColumn()) {
            return;
        }

        // Add cron jobs
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_crontab` (`name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `disable_on_error`, `end`, `inform_template`, `inform_mail`, `pluginID`) VALUES
('Cancelled baskets cleanup', 'CleanupCancelledBaskets', NULL, '', NULL, NULL, 86400, 1, 0, NULL, '', '', 0),
('Guest customer cleanup', 'CleanupGuestCustomers', NULL, '', NULL, NULL, 86400, 1, 0, NULL, '', '', 0);
EOD;
        $this->addSql($sql);

        // Add configuration
        $sql = "SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Privacy' LIMIT 1);";
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
(@formId, 'privacyGuestCustomerMonths', 'i:6;', 'Schnellbesteller ohne Bestellungen nach X Monaten löschen', 'Der Cronjob \"Guest customer cleanup\" muss hierfür aktiviert sein.', 'number', 1, 30, 0, NULL);
EOD;
        $this->addSql($sql);

        $this->addSql('SET @elementId = LAST_INSERT_ID();');

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_element_translations` 
    (`element_id`, `locale_id`, `label`, `description`)
VALUES
    (@elementId, '2', 'Delete accountless customers without orders after x months', 'The cronjob \"Guest customer cleanup\" must be active');
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
(@formId, 'privacyBasketMonths', 'i:6;', 'Abgebrochene Bestellungen nach X Monaten löschen', 'Der Cronjob \"Cancelled baskets cleanup\" muss hierfür aktiviert sein.', 'number', 1, 30, 0, NULL);
EOD;
        $this->addSql($sql);

        $this->addSql('SET @elementId2 = LAST_INSERT_ID();');

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_element_translations`
    (`element_id`, `locale_id`, `label`, `description`)
VALUES
    (@elementId2, '2', 'Delete canceled orders after x months', 'The cronjob \"Cancelled baskets cleanup\" must be active');
EOD;
        $this->addSql($sql);
    }
}
