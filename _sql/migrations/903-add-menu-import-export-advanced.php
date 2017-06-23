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

class Migrations_Migration903 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->fetchContentMenuId();
        $this->addNewMenuEntry();
    }

    private function fetchContentMenuId()
    {
        $sql = <<<SQL
SET @parentId = (
  SELECT id
  FROM s_core_menu
  WHERE name like "Inhalte"
  AND controller like "Content"
  LIMIT 1
);
SQL;
        $this->addSql($sql);
    }

    private function addNewMenuEntry()
    {
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_menu` (`id`, `parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`)
VALUES (NULL, @parentId, 'Import/Export', NULL, 'sprite-arrow-circle-double-135 contents--import-export', '3', '1', NULL, 'PluginManager', NULL, 'ImportExport');
EOD;
        $this->addSql($sql);
    }
}
