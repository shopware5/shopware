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

use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\Routing\RouterInterface;

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
                throw new \InvalidArgumentException('Argument ordernumber missing');
            }

            $categoryId = $this->Request()->get('categoryId');
            if (!$categoryId) {
                throw new \InvalidArgumentException('Argument categoryId missing');
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

        $linkRewriter = function ($link) {
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
        $this->View()->assign('sCharts', Shopware()->Modules()->Articles()->sGetArticleCharts(
            $this->Request()->getParam('sCategory')
        ));
        $this->View()->assign('perPage', $perPage);
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
            $this->View()->assign('sCloud', Shopware()->Modules()->Marketing()->sBuildTagCloud(
                $this->Request()->getParam('sCategory')
            ));
        }
    }

    /**
     * Loads the listing count for the provided listing parameters.
     * Sets a json response with: `facets`, `totalCount` and `products`.
     */
    public function listingCountAction()
    {
        if ($this->Request()->getParam('sSearch')) {
            $result = $this->fetchSearchListing();
            $this->setSearchResultResponse($result);
            return;
        }

        $categoryId = $this->Request()->getParam('sCategory');
        $productStreamId = $this->findStreamIdByCategoryId($categoryId);

        if ($productStreamId) {
            $result = $this->fetchStreamListing($productStreamId);
            $this->setSearchResultResponse($result);
            return;
        }

        $result = $this->fetchCategoryListing();
        $this->setSearchResultResponse($result);
    }

    /**
     * listing action for asynchronous fetching listing pages
     * by infinite scrolling plugin
     */
    public function ajaxListingAction()
    {
        Shopware()->Plugins()->Controller()->Json()->setPadding();

        $categoryId = $this->Request()->getParam('sCategory');
        $pageIndex = $this->Request()->getParam('sPage');

        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $productStreamId = $this->findStreamIdByCategoryId($categoryId);

        if ($productStreamId) {
            /** @var \Shopware\Components\ProductStream\CriteriaFactoryInterface $factory */
            $factory = $this->get('shopware_product_stream.criteria_factory');
            $criteria = $factory->createCriteria($this->Request(), $context);

            /** @var \Shopware\Components\ProductStream\RepositoryInterface $streamRepository */
            $streamRepository = $this->get('shopware_product_stream.repository');
            $streamRepository->prepareCriteria($criteria, $productStreamId);
        } else {
            $criteria = $this->get('shopware_search.store_front_criteria_factory')
                ->createAjaxListingCriteria($this->Request(), $context);
        }

        $articles = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryId, $criteria);
        $articles = $articles['sArticles'];

        $this->View()->loadTemplate('frontend/listing/listing_ajax.tpl');

        $layout = Shopware()->Modules()->Categories()->getProductBoxLayout($categoryId);

        $this->View()->assign([
            'sArticles' => $articles,
            'pageIndex' => $pageIndex,
            'productBoxLayout' => $layout,
            'sCategoryCurrent' => $categoryId
        ]);
    }

    /**
     * Gets a Callback-Function (callback) and the Id of an category (categoryID) from Request and read its first child-level
     */
    public function getCategoryAction()
    {
        $categoryId = $this->Request()->getParam('categoryId');
        $categoryId = (int) $categoryId;

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
     *
     * @param integer $categoryId
     * @return array
     */
    private function getCategoryById($categoryId)
    {
        $childrenIds = $this->getCategoryChildrenIds($categoryId);
        $childrenIds[] = $categoryId;

        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();
        $categories = $this->container->get('shopware_storefront.category_service')
            ->getList($childrenIds, $context);

        $converted = [];
        foreach ($categories as $category) {
            $temp = $this->container->get('legacy_struct_converter')->convertCategoryStruct($category);
            $childrenIds = $this->getCategoryChildrenIds($category->getId());
            $temp['childrenCount'] = count($childrenIds);
            $converted[$category->getId()] = $temp;
        }

        $result = $converted[$categoryId];
        unset($converted[$categoryId]);
        $result['children'] = $converted;
        $result['childrenCount'] = count($converted);

        return $result;
    }

    /**
     * @param $categoryId
     * @return array
     * @throws Exception
     */
    private function getCategoryChildrenIds($categoryId)
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select('category.id')
            ->from('s_categories', 'category')
            ->where('category.parent = :parentId')
            ->andWhere('category.active = 1')
            ->setParameter(':parentId', $categoryId);

        return $query->execute()->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param int $categoryId
     * @return int|null
     */
    private function findStreamIdByCategoryId($categoryId)
    {
        $streamId = $this->get('dbal_connection')->fetchColumn(
            'SELECT stream_id FROM s_categories WHERE id = :id',
            ['id' => $categoryId]
        );

        if ($streamId) {
            return (int) $streamId;
        }

        return null;
    }

    /**
     * @param ProductSearchResult $result
     */
    private function setSearchResultResponse(ProductSearchResult $result)
    {
        $body = [
            'totalCount' => $result->getTotalCount(),
        ];

        if ($this->Request()->getParam('loadFacets')) {
            $body['facets'] = $result->getFacets();
        }
        if ($this->Request()->getParam('loadProducts')) {
            $body['listing'] = $this->fetchListing($result);
            $body['pagination'] = $this->fetchPagination($result);
        }

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setBody(json_encode($body));
        $this->Response()->setHeader('Content-type', 'application/json', true);
    }

    /**
     * @param int $productStreamId
     * @return ProductSearchResult
     */
    private function fetchStreamListing($productStreamId)
    {
        /** @var ContextServiceInterface $contextService */
        $contextService = $this->get('shopware_storefront.context_service');
        $context = $contextService->getShopContext();

        /** @var \Shopware\Components\ProductStream\CriteriaFactoryInterface $factory */
        $factory = $this->get('shopware_product_stream.criteria_factory');
        $criteria = $factory->createCriteria($this->Request(), $context);

        /** @var \Shopware\Components\ProductStream\RepositoryInterface $streamRepository */
        $streamRepository = $this->get('shopware_product_stream.repository');
        $streamRepository->prepareCriteria($criteria, $productStreamId);

        /** @var \Shopware\Components\ProductStream\FacetFilter $facetFilter */
        $facetFilter = $this->get('shopware_product_stream.facet_filter');
        $facetFilter->add($criteria);

        $criteria->setGeneratePartialFacets(
            $this->container->get('config')->get('generatePartialFacets')
        );

        if (!$this->Request()->get('loadFacets')) {
            $criteria->resetFacets();
        }

        /** @var ProductSearchInterface $search */
        $search = $this->get('shopware_search.product_search');

        if (!$this->Request()->getParam('loadProducts')) {
            $criteria->limit(0);
        }

        $result = $search->search($criteria, $context);

        if (!$this->Request()->get('loadFacets')) {
            return $result;
        }

        return new ProductSearchResult(
            $result->getProducts(),
            $result->getTotalCount(),
            $facetFilter->filter($result->getFacets(), $criteria)
        );
    }

    /**
     * @return ProductSearchResult
     */
    private function fetchCategoryListing()
    {
        /** @var ContextServiceInterface $contextService */
        $contextService = $this->get('shopware_storefront.context_service');
        $context = $contextService->getShopContext();

        /** @var \Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface $factory */
        $factory = $this->get('shopware_search.store_front_criteria_factory');
        $criteria = $factory->createListingCriteria($this->Request(), $context);

        $criteria->setGeneratePartialFacets(
            $this->container->get('config')->get('generatePartialFacets')
        );

        if (!$this->Request()->get('loadFacets')) {
            $criteria->resetFacets();
        }

        /** @var ProductSearchInterface $search */
        $search = $this->get('shopware_search.product_search');

        if (!$this->Request()->getParam('loadProducts')) {
            $criteria->limit(0);
        }

        return $search->search($criteria, $context);
    }

    /**
     * @return ProductSearchResult
     */
    private function fetchSearchListing()
    {
        /** @var ContextServiceInterface $contextService */
        $contextService = $this->get('shopware_storefront.context_service');
        $context = $contextService->getShopContext();

        /** @var \Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface $factory */
        $factory = $this->get('shopware_search.store_front_criteria_factory');
        $criteria = $factory->createSearchCriteria($this->Request(), $context);

        $criteria->setGeneratePartialFacets(
            $this->container->get('config')->get('generatePartialFacets')
        );

        if (!$this->Request()->get('loadFacets')) {
            $criteria->resetFacets();
        }

        /** @var ProductSearchInterface $search */
        $search = $this->get('shopware_search.product_search');

        if (!$this->Request()->getParam('loadProducts')) {
            $criteria->limit(0);
        }

        return $search->search($criteria, $context);
    }

    /**
     * @param ProductSearchResult $result
     * @return string
     */
    private function fetchListing(ProductSearchResult $result)
    {
        $categoryId = $this->Request()->getParam('sCategory', null);

        $boxLayout = $categoryId ? Shopware()->Modules()->Categories()
            ->getProductBoxLayout($categoryId) : $this->get('config')->get('searchProductBoxLayout');

        $articles = $this->convertArticlesResult($result, $categoryId);

        $this->View()->assign([
            'sArticles' => $articles,
            'pageIndex' => $this->Request()->getParam('sPage'),
            'productBoxLayout' => $boxLayout,
            'sCategoryCurrent' => $categoryId
        ]);

        return $this->View()->fetch('frontend/listing/listing_ajax.tpl');
    }

    /**
     * @param ProductSearchResult $result
     * @return string
     */
    private function fetchPagination(ProductSearchResult $result)
    {
        $sPerPage = $this->Request()->getParam('sPerPage');
        $this->View()->assign([
            'sPage' => $this->Request()->getParam('sPage'),
            'pages' => ceil($result->getTotalCount() / $sPerPage),
            'baseUrl' => $this->Request()->getBaseUrl() . $this->Request()->getPathInfo(),
            'pageSizes' => explode('|', $this->container->get('config')->get('numberArticlesToShow')),
            'shortParameters' => $this->container->get('query_alias_mapper')->getQueryAliases(),
            'limit' => $sPerPage
        ]);

        return $this->View()->fetch('frontend/listing/actions/action-pagination.tpl');
    }

    /**
     * @param ProductSearchResult $result
     * @param null|int $categoryId
     * @return array
     */
    private function convertArticlesResult(ProductSearchResult $result, $categoryId)
    {
        /** @var LegacyStructConverter $converter */
        $converter = $this->get('legacy_struct_converter');
        /** @var RouterInterface $router */
        $router = $this->get('router');

        $articles = $converter->convertListProductStructList($result->getProducts());

        if (empty($articles)) {
            return $articles;
        }

        $urls = array_map(function ($article) use ($categoryId) {
            if ($categoryId !== null) {
                return $article['linkDetails'] . '&sCategory=' . (int) $categoryId;
            } else {
                return $article['linkDetails'];
            }
        }, $articles);

        $rewrite = $router->generateList($urls);

        foreach ($articles as $key => &$article) {
            if (!array_key_exists($key, $rewrite)) {
                continue;
            }
            $article['linkDetails'] = $rewrite[$key];
        }

        return $articles;
    }
}
