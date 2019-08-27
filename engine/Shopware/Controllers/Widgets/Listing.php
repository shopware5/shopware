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
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomFacet;
use Shopware\Components\Compatibility\LegacyStructConverter;

class Shopware_Controllers_Widgets_Listing extends Enlight_Controller_Action
{
    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->Response()->setHeader('x-robots-tag', 'noindex');
    }

    /**
     * Product navigation as json string
     */
    public function productNavigationAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        try {
            $orderNumber = $this->Request()->get('ordernumber');

            if (!$orderNumber) {
                throw new \InvalidArgumentException('Argument ordernumber missing');
            }

            $categoryId = (int) $this->Request()->get('categoryId');
            if (!$categoryId) {
                throw new \InvalidArgumentException('Argument categoryId missing');
            }

            if (!$this->Request()->has('sSort')) {
                $default = $this->get('config')->get('defaultListingSorting');
                $this->Request()->setParam('sSort', $default);
            }

            /** @var \sArticles $articleModule */
            $articleModule = Shopware()->Modules()->Articles();
            $navigation = $articleModule->getProductNavigation($orderNumber, $categoryId, $this->Request());

            $linkRewriter = static function ($link) {
                return Shopware()->Modules()->Core()->sRewriteLink($link);
            };

            if (isset($navigation['previousProduct'])) {
                $navigation['previousProduct']['href'] = $linkRewriter($navigation['previousProduct']['link']);
            }

            if (isset($navigation['nextProduct'])) {
                $navigation['nextProduct']['href'] = $linkRewriter($navigation['nextProduct']['link']);
            }

            $navigation['currentListing']['href'] = $linkRewriter($navigation['currentListing']['link']);
            $responseCode = 200;
            $body = json_encode($navigation, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        } catch (\InvalidArgumentException $e) {
            $responseCode = 500;
            $result = ['error' => $e->getMessage()];
            $body = json_encode($result, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        } catch (\Exception $e) {
            $responseCode = 500;
            $result = ['exception' => $e];
            $body = json_encode($result, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        }

        $this->Response()->headers->set('content-type', 'application/json', true);
        $this->Response()->setStatusCode($responseCode);
        $this->Response()->setContent($body);
    }

    /**
     * Topseller action for getting topsellers
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

    public function productsAction()
    {
        $numbers = $this->Request()->getParam('numbers');
        if (is_string($numbers)) {
            $numbers = array_filter(explode('|', $numbers));
        }

        if ($this->Request()->get('type') === 'slider') {
            $this->View()->loadTemplate('frontend/_includes/product_slider.tpl');
        } else {
            $this->View()->loadTemplate('frontend/listing/listing_ajax.tpl');
        }

        if (!is_array($numbers)) {
            return;
        }

        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();

        $products = $this->container->get('shopware_storefront.list_product_service')->getList($numbers, $context);

        $convertedProducts = $this->container->get('legacy_struct_converter')->convertListProductStructList($products);

        $this->View()->assign(['sArticles' => $convertedProducts, 'articles' => $convertedProducts]);
    }

    public function streamAction()
    {
        $streamId = $this->Request()->getParam('streamId');

        if ($this->Request()->get('type') === 'slider') {
            $this->View()->loadTemplate('frontend/_includes/product_slider.tpl');
        } else {
            $this->View()->loadTemplate('frontend/listing/listing_ajax.tpl');
        }

        if (!$streamId) {
            return;
        }

        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();

        $criteria = $this->container->get('shopware_product_stream.criteria_factory')
            ->createCriteria($this->Request(), $context);

        $this->container->get('shopware_product_stream.repository')->prepareCriteria($criteria, $streamId);

        $products = $this->container->get('shopware_search.product_search')->search($criteria, $context);

        $convertedProducts = $this->container->get('legacy_struct_converter')
            ->convertListProductStructList($products->getProducts());

        $this->View()->assign(['sArticles' => $convertedProducts, 'articles' => $convertedProducts]);
    }

    /**
     * Tag cloud by category
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

        $categoryId = (int) $this->Request()->getParam('sCategory');

        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();

        $category = $this->container->get('shopware_storefront.category_gateway')->get($categoryId, $context);

        $productStream = $category->getProductStream();

        if ($productStream) {
            $result = $this->fetchStreamListing($categoryId, $productStream->getId());
            $this->setSearchResultResponse($result);

            return;
        }

        $result = $this->fetchCategoryListing();
        $this->setSearchResultResponse($result);
    }

    /**
     * Gets a Callback-Function (callback) and the Id of an category (categoryID) from Request and read its first child-level
     */
    public function getCategoryAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');

        $category = $this->getCategoryById($categoryId);

        $this->View()->assign('category', $category);
    }

    /**
     * Gets a Callback-Function (callback) and the Id of an category (categoryID) from Request and read its first child-level
     */
    public function getCustomPageAction()
    {
        $shopPageGateway = $this->container->get('shopware_storefront.shop_page_service');
        $list = $shopPageGateway->getList(
            [(int) $this->Request()->getParam('pageId', 0)],
            $this->container->get('shopware_storefront.context_service')->getShopContext()
        );

        $list = $this->container->get('legacy_struct_converter')->convertShopPageStructList($list);
        $page = current($list);

        $this->View()->assign('customPage', [
            'parent' => $page,
            'children' => $page['children'],
        ]);
    }

    /**
     * Helper function to return the category information by category id
     *
     * @param int $categoryId
     *
     * @return array
     */
    private function getCategoryById($categoryId)
    {
        $childrenIds = $this->getCategoryChildrenIds($categoryId);
        $childrenIds[] = $categoryId;

        $context = $this->container->get('shopware_storefront.context_service')->getShopContext();
        $categories = $this->container->get('shopware_storefront.category_service')->getList($childrenIds, $context);

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
     * @param int $categoryId
     *
     * @throws Exception
     *
     * @return array
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

    private function setSearchResultResponse(ProductSearchResult $result)
    {
        $body = [
            'totalCount' => $result->getTotalCount(),
        ];

        if ($this->Request()->getParam('loadFacets')) {
            $body['facets'] = array_values($result->getFacets());
        }
        if ($this->Request()->getParam('loadProducts')) {
            $body['listing'] = $this->fetchListing($result);
            $body['pagination'] = $this->fetchPagination($result);
        }

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setContent(json_encode($body));
        $this->Response()->headers->set('content-type', 'application/json', true);
    }

    /**
     * @param int $categoryId
     * @param int $productStreamId
     *
     * @return ProductSearchResult
     */
    private function fetchStreamListing($categoryId, $productStreamId)
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

        /** @var \Shopware\Bundle\StoreFrontBundle\Service\CustomFacetServiceInterface $facetService */
        $facetService = $this->get('shopware_storefront.custom_facet_service');
        $facets = $facetService->getFacetsOfCategories([$categoryId], $context);

        /** @var CustomFacet[] $facets */
        $facets = array_shift($facets);
        foreach ($facets as $facet) {
            $criteria->addFacet($facet->getFacet());
        }

        /** @var \Shopware\Components\ProductStream\FacetFilter $facetFilter */
        $facetFilter = $this->get('shopware_product_stream.facet_filter');
        $facetFilter->add($criteria);

        $criteria->setGeneratePartialFacets(
            $this->container->get('config')->get('listingMode') === 'filter_ajax_reload'
        );

        if (!$this->Request()->get('loadFacets')) {
            $criteria->resetFacets();
        }

        /** @var ProductSearchInterface $search */
        $search = $this->get('shopware_search.product_search');

        if (!$this->Request()->getParam('loadProducts')) {
            $criteria->limit(1);
        }

        $result = $search->search($criteria, $context);

        if (!$this->Request()->get('loadFacets')) {
            return $result;
        }

        return new ProductSearchResult(
            $result->getProducts(),
            $result->getTotalCount(),
            $facetFilter->filter($result->getFacets(), $criteria),
            $criteria,
            $context
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
            $this->container->get('config')->get('listingMode') === 'filter_ajax_reload'
        );

        if (!$this->Request()->get('loadFacets')) {
            $criteria->resetFacets();
        }

        /** @var ProductSearchInterface $search */
        $search = $this->get('shopware_search.product_search');

        if (!$this->Request()->getParam('loadProducts')) {
            $criteria->limit(1);
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
            $this->container->get('config')->get('listingMode') === 'filter_ajax_reload'
        );

        if (!$this->Request()->get('loadFacets')) {
            $criteria->resetFacets();
        }

        /** @var ProductSearchInterface $search */
        $search = $this->get('shopware_search.product_search');

        if (!$this->Request()->getParam('loadProducts')) {
            $criteria->limit(1);
        }

        return $search->search($criteria, $context);
    }

    /**
     * @return string
     */
    private function fetchListing(ProductSearchResult $result)
    {
        $categoryId = (int) $this->Request()->getParam('sCategory');

        if ($this->Request()->has('productBoxLayout')) {
            $boxLayout = $this->Request()->get('productBoxLayout');
        } else {
            $boxLayout = $this->get('config')->get('searchProductBoxLayout');
            if ($categoryId) {
                $boxLayout = Shopware()->Modules()->Categories()->getProductBoxLayout($categoryId);
            }
        }

        $products = $this->convertProductsResult($result, $categoryId);

        /*
         * @deprecated
         * The assignment of all request parameters to the view below is deprecated
         * and about to be removed in 5.7
         */
        $this->View()->assign($this->Request()->getParams());

        $this->loadThemeConfig();

        $this->View()->assign([
            'sArticles' => $products,
            'pageIndex' => (int) $this->Request()->getParam('sPage'),
            'productBoxLayout' => $boxLayout,
            'sCategoryCurrent' => $categoryId,
            'sCategoryContent' => Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId),
        ]);

        $this->get('events')->notify('Shopware_Controllers_Widgets_Listing_fetchListing_preFetch', [
            'result' => $result,
            'subject' => $this,
        ]);

        return $this->View()->fetch('frontend/listing/listing_ajax.tpl');
    }

    /**
     * @return string
     */
    private function fetchPagination(ProductSearchResult $result)
    {
        $sPerPage = (int) $this->Request()->getParam('sPerPage');

        if ($sPerPage <= 0) {
            $sPerPage = 1;
        }

        $this->View()->assign([
            'sPage' => (int) $this->Request()->getParam('sPage'),
            'pages' => ceil($result->getTotalCount() / $sPerPage),
            'baseUrl' => $this->Request()->getBaseUrl() . $this->Request()->getPathInfo(),
            'pageSizes' => explode('|', $this->container->get('config')->get('numberArticlesToShow')),
            'shortParameters' => $this->container->get('query_alias_mapper')->getQueryAliases(),
            'limit' => $sPerPage,
        ]);

        $this->get('events')->notify('Shopware_Controllers_Widgets_Listing_fetchPagination_preFetch', [
            'result' => $result,
            'subject' => $this,
        ]);

        return $this->View()->fetch('frontend/listing/actions/action-pagination.tpl');
    }

    /**
     * @param int|null $categoryId
     *
     * @return array
     */
    private function convertProductsResult(ProductSearchResult $result, $categoryId)
    {
        /** @var LegacyStructConverter $converter */
        $converter = $this->get('legacy_struct_converter');

        $products = $converter->convertListProductStructList($result->getProducts());

        if (empty($products)) {
            return $products;
        }

        $useShortDescription = $this->get('config')->get('useShortDescriptionInListing');
        if ($useShortDescription) {
            foreach ($products as &$product) {
                if (strlen($product['description']) > 5) {
                    $product['description_long'] = $product['description'];
                }
            }
            unset($product);
        }

        return $this->get('shopware_storefront.listing_link_rewrite_service')->rewriteLinks(
            $result->getCriteria(),
            $products,
            $result->getContext(),
            $categoryId
        );
    }

    private function loadThemeConfig()
    {
        $inheritance = $this->container->get('theme_inheritance');

        /** @var \Shopware\Models\Shop\Shop $shop */
        $shop = $this->container->get('shop');

        $config = $inheritance->buildConfig($shop->getTemplate(), $shop, false);

        $this->get('template')->addPluginsDir(
            $inheritance->getSmartyDirectories(
                $shop->getTemplate()
            )
        );
        $this->View()->assign('theme', $config);
    }
}
