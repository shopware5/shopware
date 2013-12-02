<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

class Shopware_StoreApi_Models_Query_Criterion_PluginName extends Shopware_StoreApi_Models_Query_Criterion_Criterion
{
    /**
     * @param int|array $ids
     */
    public function __construct($pluginNames)
    {
        if(is_array($pluginNames)) {
            foreach($pluginNames as $pluginName) {
                $this->addPluginName($pluginName);
            }
        } else {
            $this->addPluginName($pluginNames);
        }
    }

    /**
     * @param string $pluginName
     * @return bool
     */
    public function addPluginName($pluginName)
    {
        if(!empty($pluginName)) {
            $this->collection[] = $pluginName;
            return true;
        } else {
            return false;
        }
    }

    public function getCriterionStatement()
    {
        if(empty($this->collection)) {
            return false;
        } else {
            return array(
                'pluginName' => $this->collection
            );
        }
    }
}
