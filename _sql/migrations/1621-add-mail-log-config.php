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

class Migrations_Migration1621 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES (0, 'mailLogActive', 'b:0;', 'Activate logging of e-mails', 'When this option is active, outgoing e-mails will be saved.', 'boolean', '0', '0', '0', NULL)
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES (0, 'mailLogActiveFilters', 'a:0:{}', 'Active mail-type-filters', 'Filters listed here will be active.', 'text', '0', '0', '0', NULL)
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES (0, 'mailLogCleanupMaximumAgeInDays', 'i:365;', 'Maximum age for log entries in days', 'The MailLogCleanup cronjob must be active for this setting to have an effect. When the cronjob is executed, entries older than configured here will be deleted.', 'number', '0', '0', '0', NULL)
SQL;
        $this->addSql($sql);
    }
}
