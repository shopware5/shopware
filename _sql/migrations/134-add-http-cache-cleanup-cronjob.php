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

class Migrations_Migration134 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @plugin_id = (SELECT id FROM s_core_plugins WHERE name='HttpCache');

INSERT IGNORE INTO `s_crontab` (`name`, `action`, `data`, `next`, `start`, `interval`, `active`, `end`, `inform_template`, `inform_mail`, `pluginID`)
VALUES ('HTTP Cache löschen', 'ClearHttpCache', '', CONCAT(CURDATE() + INTERVAL 1 DAY, ' 03:00:00'), NULL , '86400', '1', CONCAT(CURDATE() + INTERVAL 1 DAY, ' 03:00:00'), '', '', @plugin_id);

INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`,`pluginID`, `position`)
VALUES ('Shopware_CronJob_ClearHttpCache', '0', 'Shopware_Plugins_Core_HttpCacheBootstrap::onClearHttpCache', @plugin_id, '0');
EOD;
        $this->addSql($sql);
    }
}
