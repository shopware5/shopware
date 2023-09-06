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

class Migrations_Migration455 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        if ($modus === self::MODUS_INSTALL) {
            return;
        }

        $statement = $this->getConnection()->prepare("SELECT id FROM s_core_plugins WHERE name = 'HttpCache' AND active = 1 LIMIT 1;");
        $statement->execute();
        $data = $statement->fetchAll();

        if (empty($data)) {
            return;
        }

        $sql = "SET @pluginID = (SELECT id FROM s_core_plugins WHERE name = 'HttpCache' LIMIT 1);";
        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
('Shopware\\Models\\Article\\Detail::postUpdate', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @pluginID, 0);
SQL;

        $this->addSql($sql);

        $sql = <<<'SQL'
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
('Shopware\\Models\\Article\\Detail::postPersist', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist', @pluginID, 0);
SQL;

        $this->addSql($sql);
    }
}
