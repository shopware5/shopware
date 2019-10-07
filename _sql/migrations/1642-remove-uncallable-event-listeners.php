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

class Migrations_Migration1642 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $uncallableListeners = [
            'Shopware_Plugins_Backend_Menu_Bootstrap::onInitResourceMenu',
            'Shopware_Plugins_Core_Router_Bootstrap::onFilterAssemble',
            'Shopware_Plugins_Core_Router_Bootstrap::onFilterUrl',
            'Shopware_Plugins_Core_Router_Bootstrap::onAssemble',
            'Shopware_Plugins_Backend_Check_Bootstrap::onGetControllerPathBackend',
            'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::initTopSeller',
        ];

        $sql = 'DELETE FROM s_core_subscribes WHERE listener = "%s";';

        foreach ($uncallableListeners as $uncallableListener) {
            $this->addSql(sprintf($sql, $uncallableListener));
        }
    }
}
