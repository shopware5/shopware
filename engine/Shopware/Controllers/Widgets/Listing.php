<?php

declare(strict_types=1);
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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CategoryGateway;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CustomFacetServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListingLinkRewriteServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\ProductStream\CriteriaFactoryInterface;
use Shopware\Components\ProductStream\Repository;
use Shopware\Components\QueryAliasMapper;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;

class Shopware_Controllers_Widgets_Listing extends Enlight_Controller_Action
{
    public function preDispatch(): void
    {
        $this->Response()->setHeader('x-robots-tag', 'noindex');
    }

    /**
     * Product navigation as json string
     */
    public function productNavigationAction(): void
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        try {
            $orderNumber = $this->Request()->get('ordernumber');

            if (!$orderNumber) {
                throw new InvalidArgumentException('Argument ordernumber missing');
            }

            $categoryId = (int) $this->Request()->get('categoryId');
            if (!$categoryId) {
                throw new InvalidArgumentException('Argument categoryId missing');
            }

            if (!$this->Request()->has('sSort')) {
                $default = $this->container->get(Shopware_Components_Config::class)->get('defaultListingSorting');
                $this->Request()->setParam('sSort', $default);
            }

            $navigation = $this->container->get('modules')->Articles()->getProductNavigation($orderNumber, $categoryId, $this->Request());

            $linkRewriter = function ($link) {
                return $this->container->get('modules')->Core()->sRewriteLink($link);
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
        } catch (InvalidArgumentException $e) {
            $responseCode = 500;
            $result = ['error' => $e->getMessage()];
            $body = json_encode($result, JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        } catch (Exception $e) {
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
     *
     * @return void
     */
    public function topSellerAction()
    {
        $perPage = (int) $this->Request()->getParam('perPage', 4);
        $this->View()->assign('sCharts', $this->container->get('modules')->Articles()->sGetArticleCharts(
            $this->Request()->getParam('sCategory')
        ));
        $this->View()->assign('perPage', $perPage);
    }

    /**
     * @return void
     */
    public function productsAction()
    {
        $numbers = $this->Request()->getParam('numbers');
        if (\is_string($numbers)) {
            $numbers = array_filter(explode('|', $numbers));
        }

        if ($this->Request()->get('type') === 'slider') {
            $this->View()->loadTemplate('frontend/_includes/product_slider.tpl');
        } else {
            $this->View()->loadTemplate('frontend/listing/listing_ajax.tpl');
        }

        if (!\is_array($numbers)) {
            return;
        }

        $context = $this->container->get(ContextServiceInterface::class)->getShopContext();

        $products = $this->container->get(ListProductServiceInterface::class)->getList($numbers, $context);

        $convertedProducts = $this->container->get(LegacyStructConverter::class)->convertListProductStructList($products);

        $this->View()->assign(['sArticles' => $convertedProducts, 'articles' => $convertedProducts]);
    }

    /**
     * @return void
     */
    public function streamAction()
    {
        $streamId = (int) $this->Request()->getParam('streamId');

        if ($this->Request()->get('type') === 'slider') {
            $this->View()->loadTemplate('frontend/_includes/product_slider.tpl');
        } else {
            $this->View()->loadTemplate('frontend/listing/listing_ajax.tpl');
        }

        if (!$streamId) {
            return;
        }

        $context = $this->container->get(ContextServiceInterface::class)->getShopContext();

        $criteria = $this->container->get(CriteriaFactoryInterface::class)
            ->createCriteria($this->Request(), $context);

        $this->container->get(Repository::class)->prepareCriteria($criteria, $streamId);

        $products = $this->container->get(ProductSearchInterface::class)->search($criteria, $context);

        $convertedProducts = $this->container->get(LegacyStructConverter::class)
            ->convertListProductStructList($products->getProducts());

        $this->View()->assign(['sArticles' => $convertedProducts, 'articles' => $convertedProducts]);
    }

    /**
     * Tag cloud by category
     *
     * @return void
     */
    public function tagCloudAction()
    {
        $config = $this->container->get('plugins')->Frontend()->TagCloud()->Config();

        if (empty($config->get('show'))) {
            return;
        }

        $controller = $this->Request()->getParam('sController', $this->Request()->getControllerName());

        if (str_contains($config->get('controller'), $controller)) {
            $this->View()->assign('sCloud', $this->container->get('modules')->Marketing()->sBuildTagCloud(
                $this->Request()->getParam('sCategory')
            ));
        }
    }

    /**
     * Loads the listing count for the provided listing parameters.
     * Sets a json response with: `facets`, `totalCount` and `products`.
     *
     * @return void
     */
    public function listingCountAction()
    {
        $searchTerm = $this->Request()->getParam('sSearch');
        if ($searchTerm !== null) {
            $result = $this->fetchSearchListing();
            $this->setSearchResultResponse($result);

            return;
        }

        $categoryId = (int) $this->Request()->getParam('sCategory');

        $context = $this->container->get(ContextServiceInterface::class)->getShopContext();

        $category = $this->container->get(CategoryGateway::class)->get($categoryId, $context);
        if ($category instanceof Category) {
            $productStream = $category->getProductStream();

            if ($productStream) {
                $result = $this->fetchStreamListing($categoryId, $productStream->getId());
                $this->setSearchResultResponse($result);

                return;
            }
        }

        $result = $this->fetchCategoryListing();
        $this->setSearchResultResponse($result);
    }

    /**
     * @return void
     */
    public function getCategoryAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId');

        $category = $this->getCategoryById($categoryId);

        $this->View()->assign('category', $category);
    }

    /**
     * @return void
     */
    public function getCustomPageAction()
    {
        $shopPageGateway = $this->container->get('shopware_storefront.shop_page_service');
        $list = $shopPageGateway->getList(
            [(int) $this->Request()->getParam('pageId', 0)],
            $this->container->get(ContextServiceInterface::class)->getShopContext()
        );

        $list = $this->container->get(LegacyStructConverter::class)->convertShopPageStructList($list);
        $page = current($list);

        $this->View()->assign('customPage', [
            'parent' => $page,
            'children' => $page['children'],
        ]);
    }

    /**
     * Helper function to return the category information by category id
     *
     * @return array<string, mixed>
     */
    private function getCategoryById(int $categoryId): array
    {
        $childrenIds = $this->getCategoryChildrenIds($categoryId);
        $childrenIds[] = $categoryId;

        $context = $this->container->get(ContextServiceInterface::class)->getShopContext();
        $categories = $this->container->get(CategoryServiceInterface::class)->getList($childrenIds, $context);

        $converted = [];
        foreach ($categories as $category) {
            $temp = $this->container->get(LegacyStructConverter::class)->convertCategoryStruct($category);
            $childrenIds = $this->getCategoryChildrenIds($category->getId());
            $temp['childrenCount'] = \count($childrenIds);
            $converted[$category->getId()] = $temp;
        }

        $result = $converted[$categoryId];
        unset($converted[$categoryId]);
        $result['children'] = $converted;
        $result['childrenCount'] = \count($converted);

        return $result;
    }

    /**
     * @throws Exception
     *
     * @return array<int>
     */
    private function getCategoryChildrenIds(int $categoryId): array
    {
        $query = $this->container->get(Connection::class)->createQueryBuilder();
        $query->select('category.id')
            ->from('s_categories', 'category')
            ->where('category.parent = :parentId')
            ->andWhere('category.active = 1')
            ->setParameter(':parentId', $categoryId);

        return array_map('\intval', $query->execute()->fetchFirstColumn());
    }

    private function setSearchResultResponse(ProductSearchResult $result): void
    {
        $body = [
            'totalCount' => $result->getTotalCount(),
        ];

        if ($this->Request()->getParam('loadFacets')) {
            $body['facets'] = array_values($result->getFacets());
        }
        if ($this->Request()->getParam('loadProducts')) {
            $this->prepareListing($result);
            $body['listing'] = true;
            $this->preparePagination($result);
            $body['pagination'] = true;
        }
        $this->View()->assign($body);

        $this->Response()->headers->set('Shopware-Listing-Total', (string) $result->getTotalCount());
    }

    private function fetchStreamListing(int $categoryId, int $productStreamId): ProductSearchResult
    {
        $context = $this->container->get(ContextServiceInterface::class)->getShopContext();

        $criteria = $this->container->get(CriteriaFactoryInterface::class)->createCriteria($this->Request(), $context);

        $streamRepository = $this->container->get(Repository::class);
        $streamRepository->prepareCriteria($criteria, $productStreamId);

        $facets = $this->container->get(CustomFacetServiceInterface::class)->getFacetsOfCategories([$categoryId], $context);
        $facets = $facets[$categoryId];
        foreach ($facets as $facet) {
            $customFacet = $facet->getFacet();
            if ($customFacet instanceof FacetInterface) {
                $criteria->addFacet($customFacet);
            }
        }

        $facetFilter = $this->container->get('shopware_product_stream.facet_filter');
        $facetFilter->add($criteria);

        $criteria->setGeneratePartialFacets(
            $this->container->get(Shopware_Components_Config::class)->get('listingMode') === 'filter_ajax_reload'
        );

        if (!$this->Request()->get('loadFacets')) {
            $criteria->resetFacets();
        }

        $search = $this->container->get(ProductSearchInterface::class);

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

    private function fetchCategoryListing(): ProductSearchResult
    {
        $context = $this->container->get(ContextServiceInterface::class)->getShopContext();

        $criteria = $this->container->get(StoreFrontCriteriaFactoryInterface::class)->createListingCriteria($this->Request(), $context);

        $criteria->setGeneratePartialFacets(
            $this->container->get(Shopware_Components_Config::class)->get('listingMode') === 'filter_ajax_reload'
        );

        if (!$this->Request()->get('loadFacets')) {
            $criteria->resetFacets();
        }

        $search = $this->container->get(ProductSearchInterface::class);

        if (!$this->Request()->getParam('loadProducts')) {
            $criteria->limit(1);
        }

        return $search->search($criteria, $context);
    }

    private function fetchSearchListing(): ProductSearchResult
    {
        $context = $this->container->get(ContextServiceInterface::class)->getShopContext();

        $criteria = $this->container->get(StoreFrontCriteriaFactoryInterface::class)->createSearchCriteria($this->Request(), $context);

        $criteria->setGeneratePartialFacets(
            $this->container->get(Shopware_Components_Config::class)->get('listingMode') === 'filter_ajax_reload'
        );

        if (!$this->Request()->get('loadFacets')) {
            $criteria->resetFacets();
        }

        $search = $this->container->get(ProductSearchInterface::class);

        if (!$this->Request()->getParam('loadProducts')) {
            $criteria->limit(1);
        }

        return $search->search($criteria, $context);
    }

    private function prepareListing(ProductSearchResult $result): void
    {
        $categoryId = (int) $this->Request()->getParam('sCategory');

        if ($this->Request()->has('productBoxLayout')) {
            $boxLayout = $this->Request()->get('productBoxLayout');
        } else {
            $boxLayout = $this->container->get(Shopware_Components_Config::class)->get('searchProductBoxLayout');
            if ($categoryId) {
                $boxLayout = $this->container->get('modules')->Categories()->getProductBoxLayout($categoryId);
            }
        }

        $products = $this->convertProductsResult($result, $categoryId);

        $this->loadThemeConfig();

        $this->View()->assign([
            'sArticles' => $products,
            'pageIndex' => (int) $this->Request()->getParam('sPage'),
            'productBoxLayout' => $boxLayout,
            'sCategoryCurrent' => $categoryId,
            'sCategoryContent' => $this->container->get('modules')->Categories()->sGetCategoryContent($categoryId),
        ]);

        $this->container->get('events')->notify('Shopware_Controllers_Widgets_Listing_fetchListing_preFetch', [
            'result' => $result,
            'subject' => $this,
        ]);
    }

    private function preparePagination(ProductSearchResult $result): void
    {
        $sPerPage = (int) $this->Request()->getParam('sPerPage');

        if ($sPerPage <= 0) {
            $sPerPage = 1;
        }

        $this->View()->assign([
            'sPage' => (int) $this->Request()->getParam('sPage'),
            'pages' => ceil($result->getTotalCount() / $sPerPage),
            'baseUrl' => $this->Request()->getBaseUrl() . $this->Request()->getPathInfo(),
            'pageSizes' => explode('|', $this->container->get(\Shopware_Components_Config::class)->get('numberArticlesToShow')),
            'shortParameters' => $this->container->get(QueryAliasMapper::class)->getQueryAliases(),
            'limit' => $sPerPage,
        ]);

        $this->container->get('events')->notify('Shopware_Controllers_Widgets_Listing_fetchPagination_preFetch', [
            'result' => $result,
            'subject' => $this,
        ]);
    }

    /**
     * @param int|null $categoryId
     *
     * @return array<string, mixed>
     */
    private function convertProductsResult(ProductSearchResult $result, $categoryId): array
    {
        $products = $this->container->get(LegacyStructConverter::class)->convertListProductStructList($result->getProducts());

        if (empty($products)) {
            return $products;
        }

        if ($this->container->get('config')->get('useShortDescriptionInListing')) {
            foreach ($products as &$product) {
                if (\strlen($product['description']) > 5) {
                    $product['description_long'] = $product['description'];
                }
            }
            unset($product);
        }

        return $this->container->get(ListingLinkRewriteServiceInterface::class)->rewriteLinks(
            $result->getCriteria(),
            $products,
            $result->getContext(),
            $categoryId
        );
    }

    private function loadThemeConfig(): void
    {
        $shop = $this->container->get('shop');
        if (!$shop instanceof Shop) {
            return;
        }

        $template = $shop->getTemplate();
        if (!$template instanceof Template) {
            return;
        }

        $inheritance = $this->container->get('theme_inheritance');
        $config = $inheritance->buildConfig($template, $shop, false);

        $this->container->get('template')->addPluginsDir(
            $inheritance->getSmartyDirectories($template)
        );
        $this->View()->assign('theme', $config);
    }
}
