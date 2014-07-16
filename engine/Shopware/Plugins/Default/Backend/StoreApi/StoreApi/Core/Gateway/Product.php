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

class Shopware_StoreApi_Core_Gateway_Product extends Shopware_StoreApi_Core_Gateway_Gateway
{
    public function getProductRecommendations($articleId, $limit)
    {
        $json = array(
            'articleId' => $articleId,
            'limit' => $limit
        );

        return $this->get('product/recommendation', $json);
    }

    public function getProducts($start = 0, $limit = 12, $orderBy = null, $orderDirection = null, $criterion = null)
    {
        $json = array(
            'order' => array(
                'field' => $orderBy,
                'direction' => $orderDirection
            ),
            'limit' => array(
                'start' => $start,
                'limit' => $limit
            ),
            'criterion' => $criterion
        );

        return $this->get('product', $json);
    }

    public function getProductsGroupByCategories($productQueryData, $categoryQueryData)
    {
        $json = array();
        $json['product'] = array(
            'order' => array(
                'field' => $productQueryData['orderBy'],
                'direction' => $productQueryData['orderDirection']
            ),
            'limit' => array(
                'start' => $productQueryData['start'],
                'limit' => $productQueryData['limit']
            ),
            'criterion' => $productQueryData['criterion']
        );

        $json['category'] = array(
            'order' => array(
                'field' => $categoryQueryData['orderBy'],
                'direction' => $categoryQueryData['orderDirection']
            ),
            'limit' => array(
                'start' => $categoryQueryData['start'],
                'limit' => $categoryQueryData['limit']
            ),
            'criterion' => $categoryQueryData['criterion']
        );

        return $this->get('product/groupByCategories', $json);
    }

    public function getProductFeedback($articleId)
    {
        $json = array(
            'articleId' => $articleId
        );

        return $this->get('product/feedback', $json);
    }

    public function getProductUpdates($plugins)
    {
        $json = array(
            'plugins' => $plugins
        );

        return $this->get('product/updates', $json);
    }

    public function getBannerHighlights($version = null)
    {
        $json = array(
            'version' => $version
        );

        return $this->get('product/bannerHighlights', $json);
    }

    public function getCategoryHighlights($categoryId)
    {
        $json = array(
            'categoryId' => $categoryId
        );

        return $this->get('product/categoryHighlights', $json);
    }
}
