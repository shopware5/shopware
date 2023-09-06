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

namespace Shopware\Components\Snippet;

use Enlight_Config;
use Enlight_Config_Adapter_DbTable;

class DbAdapter extends Enlight_Config_Adapter_DbTable
{
    /**
     * {@inheritdoc}
     */
    public function write(Enlight_Config $config, $fields = null, $update = true, $force = false, $allowReset = false)
    {
        $this->overwriteWithDefaultShopValues($config);

        return parent::write($config, $fields, $update, $force, $allowReset);
    }

    /**
     * If there is a snippet missing, set the shopID and localeID to the main shop since all
     * language shops derive from it.
     */
    private function overwriteWithDefaultShopValues(Enlight_Config $config): void
    {
        $section = explode($config->getSectionSeparator(), $config->getSection());
        if (!\is_array($section)) {
            return;
        }

        if (!\is_array($this->_sectionColumn)) {
            return;
        }

        foreach ($this->_sectionColumn as $key => $columnName) {
            switch ($columnName) {
                case 'shopID':
                case 'localeID':
                    $section[$key] = 1;
                    break;
            }
        }
        $config->setSection($section);
    }
}
