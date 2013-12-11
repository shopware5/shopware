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

class Shopware_StoreApi_Core_Service_Category extends Enlight_Class
{
    private $gateway;

    public function init()
    {
        $this->gateway = new Shopware_StoreApi_Core_Gateway_Category();
    }

    public function getCategoryById(int $id)
    {
        $categoryQuery = new Shopware_StoreApi_Models_Query_Category();
        $categoryQuery->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_Id($id)
        );
        $searchResult = $this->getCategories($categoryQuery);

        if($searchResult instanceof Shopware_StoreApi_Exception_Response) {
            return $searchResult;
        }

        return current($searchResult->getCollection());
    }

    public function getCategories(Shopware_StoreApi_Models_Query_Category $categoryQuery)
    {
        if(!$categoryQuery instanceof Shopware_StoreApi_Models_Query_Category) {
            return new Shopware_StoreApi_Exception_Response('The parameter productModel is not instance of Shopware_StoreApi_Models_Query_Category', 10);
        }

        $start = $categoryQuery->getStart();
        $limit = $categoryQuery->getLimit();
        $orderBy = $categoryQuery->getOrderBy();
        $orderDirection = $categoryQuery->getOrderDirection();
        $criterion = $categoryQuery->getCriterion();

        return $this->gateway->getCategories($start, $limit, $orderBy, $orderDirection, $criterion);
    }

    public function getCategoriesRecursive()
    {
        return $this->gateway->getCategoriesRecursive();
    }
}
