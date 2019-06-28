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

class Migrations_Migration1620 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
SET @templateId = (
  SELECT `id`
  FROM `s_core_menu`
  WHERE `name` LIKE 'E-Mail-Vorlagen'
);
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT INTO `s_core_menu` (`id`, `parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`)
SELECT NULL, `id`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`
FROM s_core_menu
WHERE `id` = @templateId;
SQL;
        $this->addSql($sql);


        $sql = <<<'SQL'
INSERT IGNORE INTO `s_core_menu` (`parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`)
VALUES (@templateId, 'E-Mail-Log', NULL, 'sprite-inbox-document', 1, 1, NULL, 'MailLog', NULL, 'Index');
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
UPDATE `s_core_menu`
SET `name` = 'E-Mail-Verwaltung',
    `onclick` = NULL,
    `class` = 'sprite-mails',
    `active` = 1,
    `pluginID` = NULL,
    `controller` = 'MailManagement',
    `shortcut` = NULL,
    `action` = NULL
WHERE `id` = @templateId;
SQL;
        $this->addSql($sql);
    }
}
