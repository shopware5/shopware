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

class Migrations_Migration317 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            UPDATE `s_core_config_elements`
            SET `value` = 's:34:"http://store.shopware.com/storeApi";'
            WHERE `value` = 's:33:"http://store.shopware.de/storeApi";'
            AND `name` = 'StoreApiUrl';
EOD;

        $this->addSql($sql);

        $sql = <<<'EOD'
            UPDATE `s_core_config_elements`
            SET `value` = 's:72:"http://store.shopware.com/downloads/free/plugin/%name%/version/%version%";'
            WHERE `value` = 's:71:"http://store.shopware.de/downloads/free/plugin/%name%/version/%version%";'
            AND `name` = 'DummyPluginUrl';
EOD;

        $this->addSql($sql);

        $sql = <<<'EOD'
            UPDATE `s_core_config_values`
            SET `value` = 's:34:"http://store.shopware.com/storeApi";'
            WHERE `value` = 's:33:"http://store.shopware.de/storeApi";'
            AND `element_id` = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'StoreApiUrl' LIMIT 1);
EOD;

        $this->addSql($sql);

        $sql = <<<'EOD'
            UPDATE `s_core_config_values`
            SET `value` = 's:72:"http://store.shopware.com/downloads/free/plugin/%name%/version/%version%";'
            WHERE `value` = 's:71:"http://store.shopware.de/downloads/free/plugin/%name%/version/%version%";'
            AND `element_id` = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'DummyPluginUrl' LIMIT 1);
EOD;

        $this->addSql($sql);
    }
}
