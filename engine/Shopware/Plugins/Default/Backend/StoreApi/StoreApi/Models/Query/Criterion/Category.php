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

class Shopware_StoreApi_Models_Query_Criterion_Category extends Shopware_StoreApi_Models_Query_Criterion_Criterion
{
    /**
     * @param Shopware_StoreApi_Models_Category|array $categoryModels
     */
    public function __construct($categoryModels)
    {
        if(is_array($categoryModels) || $categoryModels instanceof Shopware_StoreApi_Core_Response_SearchResult) {
            foreach($categoryModels as $categoryModel) {
                $this->addCategory($categoryModel);
            }
        } else {
            $this->addCategory($categoryModels);
        }
    }

    /**
     * @param Shopware_StoreApi_Models_Category $categoryModel
     * @return bool
     */
    public function addCategory(Shopware_StoreApi_Models_Category $categoryModel)
    {
        if($categoryModel instanceof Shopware_StoreApi_Models_Category) {
            $this->collection[] = $categoryModel;
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
            $categoryIds = array();
            foreach($this->collection as $collection) {
                $categoryIds[] = $collection->getId();
            }

            return array(
                'category' => $categoryIds
            );
        }
    }
}
