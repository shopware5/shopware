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

namespace Shopware\Components\Snippet;

class DbAdapter extends \Enlight_Config_Adapter_DbTable
{
    /**
     * {@inheritdoc}
     */
    public function write(\Enlight_Config $config, $fields = null, $update = true, $force = false, $allowReset = false)
    {
        $this->overwriteWithDefaultShopValues($config);

        return parent::write($config, $fields, $update, $force, $allowReset);
    }

    /**
     * If there is a snippet missing, set the shopID and localeID to the main shop since all
     * language shops derive from it.
     */
    private function overwriteWithDefaultShopValues(\Enlight_Config $config)
    {
        $section = explode($config->getSectionSeparator(), $config->getSection());
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
