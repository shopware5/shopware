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

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;

/**
 * Shopware Listing Widgets
 */
class Shopware_Controllers_Widgets_Listing extends Enlight_Controller_Action
{
    /**
     * product navigation as json string
     */
    public function productNavigationAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        try {
            $ordernumber = $this->Request()->get('ordernumber');
            if (!$ordernumber) {
                throw new \InvalidArgumentException("Argument ordernumber missing");
            }

            $categoryId = $this->Request()->get('categoryId');
            if (!$categoryId) {
                throw new \InvalidArgumentException("Argument categoryId missing");
            }
            /** @var $articleModule \sArticles */
            $articleModule = Shopware()->Modules()->Articles();
            $navigation = $articleModule->getProductNavigation($ordernumber, $categoryId, $this->Request());
        } catch (\InvalidArgumentException $e) {
            $result = ['error' => $e->getMessage()];
            $body = json_encode($result, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            $this->Response()->setBody($body);
            $this->Response()->setHeader('Content-type', 'application/json', true);
            $this->Response()->setHttpResponseCode(500);
            return;
        } catch (\Exception $e) {
            $result = ['exception' => $e];
            $body = json_encode($result, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            $this->Response()->setBody($body);
            $this->Response()->setHeader('Content-type', 'application/json', true);
            $this->Response()->setHttpResponseCode(500);
            return;
        }

        $linkRewriter = function($link) {
            /** @var $core sCore */
            $core = Shopware()->Modules()->Core();

            return $core->sRewriteLink($link);
        };

        if (isset($navigation['previousProduct'])) {
            $navigation['previousProduct']['href'] = $linkRewriter($navigation['previousProduct']['link']);
        }

        if (isset($navigation['nextProduct'])) {
            $navigation['nextProduct']['href'] = $linkRewriter($navigation['nextProduct']['link']);
        }

        $navigation['currentListing']['href'] = $linkRewriter($navigation['currentListing']['link']);

        $body = json_encode($navigation, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $this->Response()->setBody($body);
        $this->Response()->setHeader('Content-type', 'application/json', true);
    }

    /**
     * topseller action for getting topsellers
     * by category with perPage filtering
     */
    public function topSellerAction()
    {
        $perPage = (int) $this->Request()->getParam('perPage', 4);
        $this->View()->sCharts = Shopware()->Modules()->Articles()->sGetArticleCharts(
            $this->Request()->getParam('sCategory')
        );
        $this->View()->perPage = $perPage;
    }

    /**
     * tag cloud by category
     */
    public function tagCloudAction()
    {
        $config = Shopware()->Plugins()->Frontend()->TagCloud()->Config();

        if (empty($config->show)) {
            return;
        }

        $controller = $this->Request()->getParam('sController', $this->Request()->getControllerName());

        if (strpos($config->controller, $controller) !== false) {
            $this->View()->sCloud = Shopware()->Modules()->Marketing()->sBuildTagCloud(
                $this->Request()->getParam('sCategory')
            );
        }
    }

    public function listingCountAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        $criteria = $this->get('shopware_search.store_front_criteria_factory')
            ->createAjaxCountCriteria($this->Request(), $context);

        /**@var $result ProductNumberSearchResult*/
        $result = $this->get('shopware_search.product_number_search')->search(
            $criteria,
            $context
        );

        $body = json_encode(array('totalCount' => $result->getTotalCount()));
        $this->Response()->setBody($body);
        $this->Response()->setHeader('Content-type', 'application/json', true);
    }

    /**
     * listing action for asynchronous fetching listing pages
     * by infinite scrolling plugin
     */
    public function ajaxListingAction()
    {
        Enlight()->Plugins()->Controller()->Json()->setPadding();

        $categoryId = $this->Request()->getParam('sCategory');
        $pageIndex = $this->Request()->getParam('sPage');

        $context = Shopware()->Container()->get('shopware_storefront.context_service')
            ->getProductContext();

        $criteria = Shopware()->Container()->get('shopware_search.store_front_criteria_factory')
            ->createAjaxListingCriteria($this->Request(), $context);

        $articles = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryId, $criteria);
        $articles = $articles['sArticles'];

        $this->View()->loadTemplate('frontend/listing/listing_ajax.tpl');

        $layout = Shopware()->Modules()->Categories()->getProductBoxLayout($categoryId);

        $this->View()->assign(array(
            'sArticles' => $articles,
            'pageIndex' => $pageIndex,
            'productBoxLayout' => $layout
        ));
    }

    /**
     * Gets a Callback-Function (callback) and the Id of an category (categoryID) from Request and read its first child-level
     */
    public function getCategoryAction()
    {
        $categoryId = $this->Request()->getParam('categoryId');
        $categoryId = intval($categoryId);

        $category = $this->getCategoryById($categoryId);

        $this->View()->assign('category', $category);
    }

    /**
     * Gets a Callback-Function (callback) and the Id of an category (categoryID) from Request and read its first child-level
     */
    public function getCustomPageAction()
    {
        $pageId = (int) $this->Request()->getParam('pageId', 0);
        $groupKey = $this->Request()->getParam('groupKey', 'gLeft');

        $customPage = Shopware()->Modules()->Cms()->sGetStaticPageChildrensById($pageId, $groupKey);

        $this->View()->assign('customPage', $customPage);
    }

    /**
     * Helper function to return the category information by category id
     * @param integer $categoryId
     * @return mixed
     */
    private function getCategoryById($categoryId)
    {
        /** @var \Shopware\Models\Category\Repository $categoryRepository */
        $categoryRepository = $this->get('models')->getRepository('Shopware\Models\Category\Category');
        $category = $categoryRepository->getCategoryByIdQuery($categoryId)->getArrayResult();

        if (empty($category)) {
            return array();
        }

        $category = $category[0];

        $category['link'] = $this->getCategoryLink($categoryId, $category['blog']);

        foreach ($category['children'] as &$child) {
            $child['link'] = $this->getCategoryLink($child['id'], $child['blog']);

            // search for childrens
            $childrenOfChildren = $categoryRepository->getCategoryByIdQuery($child['id'])->getArrayResult();
            $childrenOfChildren = $childrenOfChildren[0]['children'];

            $child['childrenCount'] = count($childrenOfChildren);
        }

        return $category;
    }

    /**
     * Helper function to create a category link
     * @param integer $categoryId
     * @param string $categoryName
     * @param bool $blog
     * @return mixed|string
     */
    private function getCategoryLink($categoryId, $blog = false)
    {
        return $this->get('config')->get('baseFile') . '?' . http_build_query([
            'sViewport' => $blog ? 'blog' : 'cat',
            'sCategory' => $categoryId
        ], '', '&');
    }
}
