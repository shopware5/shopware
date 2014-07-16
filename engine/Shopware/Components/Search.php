<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * Shopware Search Adapter Proxy
 */
class Shopware_Components_Search extends Enlight_Class implements Enlight_Hook
{
    /**
     * @var Shopware_Components_Search_Adapter_Abstract
     */
    protected $adapter;

    /**
     * @param Shopware_Components_Search_Adapter_Abstract $adapter
     */
    public function __construct(Shopware_Components_Search_Adapter_Abstract $adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * @param Shopware_Components_Search_Adapter_Abstract $adapter
     */
    public function setAdapter(Shopware_Components_Search_Adapter_Abstract $adapter)
    {
        $this->adapter = $adapter;
    }


    /**
    * @return \Shopware_Components_Search_Adapter_Abstract
    */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
    * @param $term string
    * @return Shopware_Components_Search_Result_Abstract
    */
    public function search($term, array $config)
    {
        return $this->getAdapter()->search($term,$config);
    }
}
