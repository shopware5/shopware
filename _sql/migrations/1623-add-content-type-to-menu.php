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

class Migrations_Migration1623 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_core_menu`
ADD `content_type` varchar(255) COLLATE \'utf8_unicode_ci\' NULL;
');

        $this->addSql('INSERT INTO `s_core_menu` (`parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`, `content_type`)
VALUES (\'23\', \'Inhaltstypen\', NULL, \'sprite-application-form\', \'0\', \'1\', NULL, \'ContentTypeManager\', NULL, \'index\', NULL);');

        $this->addSql('CREATE TABLE `s_content_types` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `internalName` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `config` text NOT NULL
);');

        $this->addSql("INSERT IGNORE INTO `s_core_acl_resources` (name) VALUES ('contenttypemanager');");

        $this->addSql('SET @resourceId = LAST_INSERT_ID();');

        $this->addSql("INSERT IGNORE INTO `s_core_acl_privileges` (resourceID,name) VALUES (@resourceId, 'read');");
        $this->addSql("INSERT IGNORE INTO `s_core_acl_privileges` (resourceID,name) VALUES (@resourceId, 'edit');");
        $this->addSql("INSERT IGNORE INTO `s_core_acl_privileges` (resourceID,name) VALUES (@resourceId, 'delete');");
    }
}
