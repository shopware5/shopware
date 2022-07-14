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

class Migrations_Migration785 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->updateHelpMenuPosition();

        $sql = <<<'EOD'
SELECT id FROM s_core_menu WHERE name = "Connect" LIMIT 1;
EOD;
        $menuId = $this->connection->query($sql)->fetch();
        if ($menuId) {
            return;
        }

        $this->addConnectMenu();
    }

    private function updateHelpMenuPosition()
    {
        $sql = <<<'EOD'
    UPDATE `s_core_menu` SET `position`= 999 WHERE `name` = '' AND `class` = 'ico question_frame shopware-help-menu';
EOD;
        $this->addSql($sql);
    }

    private function addConnectMenu()
    {
        $sql = <<<'EOD'
INSERT INTO `s_core_menu` (`id`, `parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`)
VALUES (NULL, NULL, 'Connect', NULL, 'shopware-connect', '0', '1', NULL, NULL, NULL, NULL);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_core_menu` WHERE `name` = 'Connect');

INSERT INTO `s_core_menu` (`id`, `parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`)
VALUES (NULL, @parent, 'Einstieg', NULL, 'sprite-mousepointer-click', '0', '1', NULL, 'PluginManager', NULL, 'ShopwareConnect');
EOD;
        $this->addSql($sql);
    }
}
