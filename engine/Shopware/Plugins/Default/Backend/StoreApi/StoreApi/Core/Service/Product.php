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

class Shopware_StoreApi_Core_Service_Product extends Enlight_Class
{
    /**
     * Holds the gateway of the service layer
     * @var Shopware_StoreApi_Core_Gateway_Product
     */
    private $gateway;

    public function init()
    {
        $this->gateway = new Shopware_StoreApi_Core_Gateway_Product();
    }

    public function getProductById(int $id)
    {
        $productQuery = new Shopware_StoreApi_Models_Query_Product();
        $productQuery->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_Id($id)
        );
        $searchResult = $this->getProducts($productQuery);

        if ($searchResult instanceof Shopware_StoreApi_Exception_Response) {
            return $searchResult;
        }

        return current($searchResult->getCollection());
    }

    public function getProductRecommendations(Shopware_StoreApi_Models_Product $productModel, $limit = 5)
    {
        if (!$productModel instanceof Shopware_StoreApi_Models_Product) {
            return new Shopware_StoreApi_Exception_Response('The parameter productModel is not instance of Shopware_StoreApi_Models_Product', 10);
        }

        return $this->gateway->getProductRecommendations($productModel->getId(), $limit);
    }

    public function getProducts(Shopware_StoreApi_Models_Query_Product $productQuery)
    {
        if (!$productQuery instanceof Shopware_StoreApi_Models_Query_Product) {
            return new Shopware_StoreApi_Exception_Response('The parameter productModel is not instance of Shopware_StoreApi_Models_Query_Product', 10);
        }

        $start = $productQuery->getStart();
        $limit = $productQuery->getLimit();
        $orderBy = $productQuery->getOrderBy();
        $orderDirection = $productQuery->getOrderDirection();
        $criterion = $productQuery->getCriterion();

        return $this->gateway->getProducts($start, $limit, $orderBy, $orderDirection, $criterion);
    }

    public function getProductsGroupByCategories(Shopware_StoreApi_Models_Query_Product $productQuery, Shopware_StoreApi_Models_Query_Category $categoryQuery)
    {
        if (!$productQuery instanceof Shopware_StoreApi_Models_Query_Product) {
            return new Shopware_StoreApi_Exception_Response('The parameter productModel is not instance of Shopware_StoreApi_Models_Query_Product', 10);
        }
        if (!$categoryQuery instanceof Shopware_StoreApi_Models_Query_Category) {
            return new Shopware_StoreApi_Exception_Response('The parameter productModel is not instance of Shopware_StoreApi_Models_Query_Category', 10);
        }

        $productQueryData = array();
        $productQueryData['start']          = $productQuery->getStart();
        $productQueryData['limit']          = $productQuery->getLimit();
        $productQueryData['orderBy']        = $productQuery->getOrderBy();
        $productQueryData['orderDirection'] = $productQuery->getOrderDirection();
        $productQueryData['criterion']      = $productQuery->getCriterion();

        $categoryQueryData = array();
        $categoryQueryData['start']          = $categoryQuery->getStart();
        $categoryQueryData['limit']          = $categoryQuery->getLimit();
        $categoryQueryData['orderBy']        = $categoryQuery->getOrderBy();
        $categoryQueryData['orderDirection'] = $categoryQuery->getOrderDirection();
        $categoryQueryData['criterion']      = $categoryQuery->getCriterion();

        return $this->gateway->getProductsGroupByCategories($productQueryData, $categoryQueryData);
    }

    public function getProductFeedback(Shopware_StoreApi_Models_Product $productModel)
    {
        if (!$productModel instanceof Shopware_StoreApi_Models_Product) {
            return new Shopware_StoreApi_Exception_Response('The parameter productModel is not instance of Shopware_StoreApi_Models_Product', 10);
        }

        return $this->gateway->getProductFeedback($productModel->getId());
    }

    public function getProductUpdates($plugins)
    {
        if (!is_array($plugins)) {
            return new Shopware_StoreApi_Exception_Response('The parameter plugins is not an array', 10);
        }

        return $this->gateway->getProductUpdates($plugins);
    }

    public function getBannerHighlights($version = null)
    {
        if (!is_integer($version) && $version != null) {
            return new Shopware_StoreApi_Exception_Response('The parameter version is not instance of Integer', 10);
        }
        return $this->gateway->getBannerHighlights($version);
    }

    public function getCategoryHighlights(Shopware_StoreApi_Models_Category $categoryModel)
    {
        if (!$categoryModel instanceof Shopware_StoreApi_Models_Category) {
            return new Shopware_StoreApi_Exception_Response('The parameter categoryModel is not instance of Shopware_StoreApi_Models_Category', 10);
        }

        return $this->gateway->getCategoryHighlights($categoryModel->getId());
    }
}
