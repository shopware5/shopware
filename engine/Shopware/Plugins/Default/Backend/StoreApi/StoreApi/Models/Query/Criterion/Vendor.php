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

class Shopware_StoreApi_Models_Query_Criterion_Vendor extends Shopware_StoreApi_Models_Query_Criterion_Criterion
{
    /**
     * @param Shopware_StoreApi_Models_Vendor|array $vendorModels
     */
    public function __construct($vendorModels)
    {
        if(is_array($vendorModels) || $vendorModels instanceof Shopware_StoreApi_Core_Response_SearchResult) {
            foreach($vendorModels as $vendorModel) {
                $this->addVendor($vendorModel);
            }
        } else {
            $this->addVendor($vendorModels);
        }
    }

    /**
     * @param Shopware_StoreApi_Models_Vendor $vendorModel
     * @return bool
     */
    public function addVendor(Shopware_StoreApi_Models_Vendor $vendorModel)
    {
        if($vendorModel instanceof Shopware_StoreApi_Models_Vendor) {
            $this->collection[] = $vendorModel;
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
            $vendorIds = array();
            foreach($this->collection as $collection) {
                $vendorIds[] = $collection->getId();
            }

            return array(
                'vendor' => $vendorIds
            );
        }
    }
}
