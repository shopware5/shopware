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

use Enlight_Controller_Request_RequestHttp as Request;
use Shopware\Bundle\ControllerBundle\Exceptions\ResourceNotFoundException;
use Shopware\Bundle\EmotionBundle\Service\StoreFrontEmotionDeviceConfigurationInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CustomFacetServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CustomSortingServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ManufacturerServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\ProductStream\CriteriaFactoryInterface;
use Shopware\Components\ProductStream\FacetFilterInterface;
use Shopware\Components\ProductStream\RepositoryInterface as ProductStreamRepositoryInterface;
use Shopware\Models\Category\Repository as CategoryRepository;
use Shopware\Models\CustomerStream\CustomerStreamRepositoryInterface;
use Shopware\Models\Emotion\Emotion;

class Shopware_Controllers_Frontend_Listing extends Enlight_Controller_Action
{
    private CustomerStreamRepositoryInterface $customerStreamRepository;

    private ContextServiceInterface $contextService;

    private StoreFrontEmotionDeviceConfigurationInterface $storeFrontEmotionDeviceConfiguration;

    private Shopware_Components_Config $config;

    private StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory;

    private ProductNumberSearchInterface $productNumberSearch;

    private CustomSortingServiceInterface $customSortingService;

    private CriteriaFactoryInterface $criteriaFactory;

    private CustomFacetServiceInterface $customFacetService;

    private FacetFilterInterface $facetFilter;

    private ManufacturerServiceInterface $manufacturerService;

    private CategoryRepository $categoryRepository;

    private ProductStreamRepositoryInterface $productStreamRepository;

    public function __construct(
        CustomerStreamRepositoryInterface $customerStreamRepository,
        ContextServiceInterface $contextService,
        StoreFrontEmotionDeviceConfigurationInterface $storeFrontEmotionDeviceConfiguration,
        Shopware_Components_Config $config,
        StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory,
        ProductNumberSearchInterface $productNumberSearch,
        CustomSortingServiceInterface $customSortingService,
        CriteriaFactoryInterface $criteriaFactory,
        CustomFacetServiceInterface $customFacetService,
        FacetFilterInterface $facetFilter,
        ManufacturerServiceInterface $manufacturerService,
        CategoryRepository $categoryRepository,
        ProductStreamRepositoryInterface $productStreamRepository
    ) {
        $this->customerStreamRepository = $customerStreamRepository;
        $this->contextService = $contextService;
        $this->storeFrontEmotionDeviceConfiguration = $storeFrontEmotionDeviceConfiguration;
        $this->config = $config;
        $this->storeFrontCriteriaFactory = $storeFrontCriteriaFactory;
        $this->productNumberSearch = $productNumberSearch;
        $this->customSortingService = $customSortingService;
        $this->criteriaFactory = $criteriaFactory;
        $this->customFacetService = $customFacetService;
        $this->facetFilter = $facetFilter;
        $this->manufacturerService = $manufacturerService;
        $this->categoryRepository = $categoryRepository;
        $this->productStreamRepository = $productStreamRepository;
    }

    /**
     * @return void
     */
    public function indexAction(Request $request, ShopContextInterface $shopContext)
    {
        $requestCategoryId = (int) $request->getParam('sCategory');

        $categoryContent = $this->loadCategoryContent($requestCategoryId, $request);

        $emotionConfiguration = $this->getEmotionConfiguration(
            $requestCategoryId,
            false,
            $categoryContent['streamId']
        );

        $location = $this->getRedirectLocation($categoryContent, $emotionConfiguration['hasEmotion'], $shopContext);
        if (!empty($location)) {
            $this->redirect($location, ['code' => 301]);

            return;
        }

        $hasCustomerStreamEmotions = $this->customerStreamRepository
            ->hasCustomerStreamEmotions($requestCategoryId);

        if ($hasCustomerStreamEmotions && !$request->getParam('sPage')) {
            $assign = $this->View()->getAssign();
            $this->View()->loadTemplate('frontend/listing/customer_stream.tpl');
            $this->View()->assign($assign);

            return;
        }

        $this->View()->assign($emotionConfiguration);

        // Only show the listing if an emotion viewport is empty or the showListing option is active
        if (!$this->loadListing($emotionConfiguration, $request)) {
            return;
        }

        $this->loadCategoryListing($requestCategoryId, $categoryContent, $shopContext);
    }

    /**
     * @return void
     */
    public function layoutAction(Request $request)
    {
        $this->View()->loadTemplate('frontend/listing/customer_stream/layout.tpl');

        $categoryId = (int) $request->getParam('sCategory');

        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);

        $config = $this->getEmotionConfiguration($categoryId, true, $categoryContent['streamId']);

        $config = array_merge($config, [
            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($categoryId),
            'sCategoryContent' => $categoryContent,
            'Controller' => 'listing',
            'params' => $this->Request()->getParams(),
        ]);

        $this->View()->assign($config);
    }

    /**
     * @return void
     */
    public function listingAction(Request $request, ShopContextInterface $context)
    {
        $this->View()->loadTemplate('frontend/listing/customer_stream/listing.tpl');

        $requestCategoryId = (int) $request->getParam('sCategory');

        $categoryContent = $this->loadCategoryContent($requestCategoryId, $request);

        $this->loadCategoryListing($requestCategoryId, $categoryContent, $context);
    }

    /**
     * Listing of all manufacturer products.
     * Templates extends from the normal listing template.
     *
     * @return void
     */
    public function manufacturerAction(ShopContextInterface $context)
    {
        $manufacturerId = $this->Request()->getParam('sSupplier');

        if (!$this->Request()->getParam('sCategory')) {
            $categoryId = $context->getShop()->getCategory()->getId();

            $this->Request()->setParam('sCategory', $categoryId);

            $sortings = $this->customSortingService->getSortingsOfCategories([$categoryId], $context);

            $sortings = array_shift($sortings);

            $this->setDefaultSorting($sortings);

            $this->view->assign('sortings', $sortings);
        }

        $criteria = $this->storeFrontCriteriaFactory
            ->createListingCriteria($this->Request(), $context);

        if ($condition = $criteria->getCondition('manufacturer')) {
            $criteria->removeCondition('manufacturer');
            $criteria->addBaseCondition($condition);
        }

        $categoryProducts = Shopware()->Modules()->Articles()->sGetArticlesByCategory(
            $context->getShop()->getCategory()->getId(),
            $criteria
        );
        if (!\is_array($categoryProducts)) {
            $categoryProducts = ['facets' => []];
        }

        $manufacturer = $this->manufacturerService->get(
            $manufacturerId,
            $context
        );

        if ($manufacturer === null) {
            throw new Enlight_Controller_Exception('Manufacturer missing, non-existent or invalid', 404);
        }

        $facets = [];
        foreach ($categoryProducts['facets'] as $facet) {
            if (!$facet instanceof FacetResultInterface || $facet->getFacetName() === 'manufacturer') {
                continue;
            }
            $facets[] = $facet;
        }

        $categoryProducts['facets'] = $facets;

        $this->View()->assign($categoryProducts);
        $this->View()->assign('showListing', true);
        $this->View()->assign('manufacturer', $manufacturer);
        $this->View()->assign('ajaxCountUrlParams', [
            'sSupplier' => $manufacturerId,
            'sCategory' => $context->getShop()->getCategory()->getId(),
        ]);

        $this->View()->assign('sCategoryContent', $this->getSeoDataOfManufacturer($manufacturer));
    }

    /**
     * Returns listing breadcrumb
     *
     * @param int $categoryId
     *
     * @return array listing breadcrumb
     *
     * @deprecated in 5.6, will be private in 5.8
     */
    public function getBreadcrumb($categoryId)
    {
        $breadcrumb = Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId);

        return array_reverse($breadcrumb);
    }

    /**
     * @deprecated in 5.7, will be private in 5.8
     *
     * @param int  $categoryId
     * @param bool $withStreams
     * @param int  $streamId
     *
     * @return array{emotions: array, hasEmotion: bool, showListing: bool, isHomePage: bool, showListingButton: bool}
     */
    protected function getEmotionConfiguration($categoryId, $withStreams = false, $streamId = null)
    {
        $context = $this->contextService->getShopContext();

        $emotions = $this->storeFrontEmotionDeviceConfiguration->getCategoryConfiguration($categoryId, $context, $withStreams);

        $emotions = $this->filterListingEmotions($emotions);

        $isHomePage = $context->getShop()->getCategory()->getId() === $categoryId;

        $devicesWithListing = $this->getDevicesWithListing($emotions);
        if ($isHomePage) {
            $devicesWithListing = [];
        }

        return [
            'emotions' => $emotions,
            'hasEmotion' => !empty($emotions),
            'showListing' => $this->hasListing($emotions, $devicesWithListing),
            'showListingDevices' => $devicesWithListing,
            'isHomePage' => $isHomePage,
            'showListingButton' => $this->hasProducts($categoryId, $context, $streamId),
        ];
    }

    private function hasProducts(int $categoryId, ShopContextInterface $context, ?int $streamId): bool
    {
        if ($this->Request()->getParam('sPage')) {
            return false;
        }

        if ($streamId) {
            $criteria = $this->createCategoryStreamCriteria($categoryId, $streamId, $context);
        } else {
            $criteria = $this->storeFrontCriteriaFactory
                ->createListingCriteria($this->Request(), $context);
        }

        // Creating the criteria above will also set the sPage param to at least 1, which we don't want
        $this->Request()->setParam('sPage', null);

        // Performance increase
        $criteria->setFetchCount(false);
        $criteria->resetFacets();
        $criteria->limit(1);

        $numberResult = $this->productNumberSearch->search($criteria, $context);

        return $numberResult->getTotalCount() > 0;
    }

    /**
     * @param array<string, mixed> $categoryContent
     *
     * @return array{controller?: string, sArticle?: int}
     */
    private function getRedirectLocation(array $categoryContent, bool $hasEmotion, ShopContextInterface $context): array
    {
        $location = [];

        $checkRedirect = ($hasEmotion && $this->Request()->getParam('sPage')) || (!$hasEmotion);

        if (!empty($categoryContent['external'])) {
            $location = $categoryContent['external'];
        } elseif ($this->isShopsBaseCategoryPage($categoryContent['id'])) {
            $location = ['controller' => 'index'];
        } elseif ($checkRedirect && $this->config->get('categoryDetailLink')) {
            $criteria = $this->storeFrontCriteriaFactory->createListingCriteria($this->Request(), $context);

            $criteria->resetFacets()
                ->resetConditions()
                ->resetSorting()
                ->offset(0)
                ->limit(2)
                ->setFetchCount(false);

            $result = $this->productNumberSearch->search($criteria, $context);

            if (\count($result->getProducts()) === 1) {
                $products = $result->getProducts();
                $first = array_shift($products);
                $location = ['controller' => 'detail', 'sArticle' => $first->getId()];
            }
        }

        return $location;
    }

    /**
     * Converts the provided manufacturer to the category seo data structure.
     * Result can be merged with "sCategoryContent" to override relevant seo category data with
     * manufacturer data.
     *
     * @return array{metaDescription: string, metaKeywords: string, canonicalParams: array{sViewport: string, sAction: string, sSupplier: int}, metaTitle: string, title: string, productBoxLayout: string}
     */
    private function getSeoDataOfManufacturer(Manufacturer $manufacturer): array
    {
        $content = [];

        $content['metaDescription'] = $manufacturer->getMetaDescription();
        $content['metaKeywords'] = $manufacturer->getMetaKeywords();

        $canonicalParams = [
            'sViewport' => 'listing',
            'sAction' => 'manufacturer',
            'sSupplier' => $manufacturer->getId(),
        ];

        $content['canonicalParams'] = $canonicalParams;
        $content['metaTitle'] = $manufacturer->getMetaTitle();
        $content['title'] = $manufacturer->getName();
        $content['productBoxLayout'] = $this->config->get('manufacturerProductBoxLayout');

        return $content;
    }

    /**
     * Checks if the provided $categoryId is in the current shop's category tree
     */
    private function isValidCategoryPath(int $categoryId): bool
    {
        $defaultShopCategoryId = Shopware()->Shop()->getCategory()->getId();

        $categoryPath = $this->categoryRepository->getPathById($categoryId);

        if (\is_string($categoryPath)) {
            return true;
        }

        if (!\array_key_exists($defaultShopCategoryId, $categoryPath)) {
            $this->Request()->setQuery('sCategory', $defaultShopCategoryId);
            $this->Response()->setStatusCode(404);

            return false;
        }

        return true;
    }

    /**
     * Helper function used in the listing action to detect if
     * the user is trying to open the page matching the shop's root category
     */
    private function isShopsBaseCategoryPage(int $categoryId): bool
    {
        $defaultShopCategoryId = Shopware()->Shop()->getCategory()->getId();

        $queryParamsWhiteList = ['controller', 'action', 'sCategory', 'sViewport', 'rewriteUrl', 'module'];
        $queryParamsNames = array_keys($this->Request()->getParams());
        $paramsDiff = array_diff($queryParamsNames, $queryParamsWhiteList);

        return $defaultShopCategoryId === $categoryId && !$paramsDiff;
    }

    /**
     * Determines if the product listing has to be loaded/shown at all
     *
     * @param array<string, mixed> $emotions
     */
    private function hasListing(array $emotions, array $devicesWithEmotion): bool
    {
        if ($this->Request()->getParam('sPage')) {
            return true;
        }

        if (empty($emotions)) {
            return true;
        }

        $showListing = array_column($emotions, 'showListing');

        if (!empty($showListing) && (bool) max($showListing)) {
            return true;
        }

        // Enable the listing if there's a viewport with no emotion assigned
        if (!$this->haveAllViewportsEmotions($emotions)) {
            return true;
        }

        if (empty($devicesWithEmotion)) {
            return false;
        }

        $entryPageEmotions = array_filter($emotions, function ($emotion) {
            return \in_array($emotion['listing_visibility'], [
                Emotion::LISTING_VISIBILITY_ONLY_START,
                Emotion::LISTING_VISIBILITY_ONLY_START_AND_LISTING,
            ], true);
        });

        return empty($entryPageEmotions);
    }

    /**
     * Filters the device types down to which have to show the product listing
     *
     * @param array<string, mixed> $emotions
     *
     * @return int[]
     */
    private function getDevicesWithListing(array $emotions): array
    {
        if ($this->Request()->getParam('sPage')) {
            return [];
        }

        $visibleDevices = [0, 1, 2, 3, 4];
        $permanentVisibleDevices = [];

        foreach ($emotions as $emotion) {
            // Always show the listing in the emotion viewports when the option "show listing" is active
            if ($emotion['showListing']) {
                $permanentVisibleDevices = array_merge($permanentVisibleDevices, $emotion['devicesArray']);
            }

            $visibleDevices = array_diff($visibleDevices, $emotion['devicesArray']);
        }

        $visibleDevices = array_merge($permanentVisibleDevices, $visibleDevices);

        return array_values($visibleDevices);
    }

    /**
     * @param ?CustomSorting[] $sortings
     */
    private function setDefaultSorting(?array $sortings): void
    {
        if ($this->Request()->has('sSort')) {
            return;
        }

        if (!\is_array($sortings)) {
            return;
        }

        $default = array_shift($sortings);

        if (!$default) {
            return;
        }

        $this->Request()->setParam('sSort', $default->getId());
    }

    private function createCategoryStreamCriteria(int $categoryId, int $streamId, ShopContextInterface $context): Criteria
    {
        $criteria = $this->criteriaFactory->createCriteria($this->Request(), $context);

        $this->productStreamRepository->prepareCriteria($criteria, $streamId);

        $facets = $this->customFacetService->getFacetsOfCategories([$categoryId], $context);

        $facets = array_shift($facets);

        if (!\is_array($facets)) {
            $facets = [];
        }

        foreach ($facets as $facet) {
            $customFacet = $facet->getFacet();

            if ($customFacet instanceof CustomFacet) {
                $criteria->addFacet($customFacet);
            }
        }

        $this->facetFilter->add($criteria);

        $criteria->removeFacet('category');

        return $criteria;
    }

    /**
     * @param array<string, mixed> $categoryContent
     */
    private function loadCategoryListing(int $categoryId, array $categoryContent, ShopContextInterface $context): void
    {
        $sortings = $this->customSortingService->getSortingsOfCategories([$categoryId], $context);
        $sortings = array_shift($sortings);

        $this->setDefaultSorting($sortings);

        if ($categoryContent['streamId']) {
            $criteria = $this->createCategoryStreamCriteria($categoryId, $categoryContent['streamId'], $context);
        } else {
            $criteria = $this->storeFrontCriteriaFactory->createListingCriteria($this->Request(), $context);
        }

        if ($categoryContent['hideFilter']) {
            $criteria->resetFacets();
        }

        $categoryProducts = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryId, $criteria);

        if (!$categoryProducts || ($categoryProducts['sPage'] > 1 && empty($categoryProducts['sArticles']))) {
            throw new Enlight_Controller_Exception('Listing page is empty', Enlight_Controller_Exception::Controller_Dispatcher_Controller_Not_Found);
        }

        if ($this->Request()->getParam('sRss') || $this->Request()->getParam('sAtom')) {
            $viewData = $this->View()->getAssign();
            $this->Response()->headers->set('content-type', 'text/xml');
            $type = $this->Request()->getParam('sRss') ? 'rss' : 'atom';
            $this->View()->loadTemplate('frontend/listing/' . $type . '.tpl');
            $this->View()->assign($viewData);
        }

        $facets = $this->facetFilter->filter($categoryProducts['facets'], $criteria);
        $categoryProducts['facets'] = $facets;

        $this->View()->assign($categoryProducts);
        $this->View()->assign('sortings', $sortings);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadCategoryContent(int $requestCategoryId, Request $request): array
    {
        if (empty($requestCategoryId) || !$this->isValidCategoryPath($requestCategoryId)) {
            throw new Enlight_Controller_Exception('Listing category missing, non-existent or invalid for the current shop', 404);
        }

        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($requestCategoryId);

        if (!\is_array($categoryContent)) {
            throw new ResourceNotFoundException('Category not found', $this->Request());
        }

        // Check if the requested category-id belongs to a blog category
        if ($categoryContent['blog']) {
            throw new Enlight_Controller_Exception('Listing category missing, non-existent or invalid for the current shop', 404);
        }

        $request->query->set('sCategory', $requestCategoryId);

        $this->View()->assign([
            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($requestCategoryId),
            'sBreadcrumb' => $this->getBreadcrumb($requestCategoryId),
            'sCategoryContent' => $categoryContent,
            'activeFilterGroup' => $request->getQuery('sFilterGroup'),
            'ajaxCountUrlParams' => ['sCategory' => $categoryContent['id']],
            'params' => $request->getParams(),
        ]);

        if (!empty($categoryContent['template'])) {
            if ($this->View()->templateExists('frontend/listing/' . $categoryContent['template'])) {
                $vars = $this->View()->getAssign();
                $this->View()->loadTemplate('frontend/listing/' . $categoryContent['template']);
                $this->View()->assign($vars);
            } else {
                $this->get('corelogger')->error(
                    'Missing category template detected. Please correct the template for category "' . $categoryContent['name'] . '".',
                    [
                        'uri' => $this->Request()->getRequestUri(),
                        'categoryId' => $requestCategoryId,
                        'categoryName' => $categoryContent['name'],
                    ]
                );
            }
        }

        return $categoryContent;
    }

    /**
     * @param array{showListing: bool} $emotionConfiguration
     */
    private function loadListing(array $emotionConfiguration, Request $request): bool
    {
        return $emotionConfiguration['showListing'] || $request->getParam('sPage');
    }

    /**
     * @param array<string, mixed> $emotions
     */
    private function filterListingEmotions(array $emotions): array
    {
        if ((int) $this->Request()->getParam('sPage') > 0) {
            return array_filter($emotions, function ($emotion) {
                return \in_array($emotion['listing_visibility'], [
                    Emotion::LISTING_VISIBILITY_ONLY_LISTING,
                    Emotion::LISTING_VISIBILITY_ONLY_START_AND_LISTING,
                ], true);
            });
        }

        return array_filter($emotions, function ($emotion) {
            return \in_array($emotion['listing_visibility'], [
                Emotion::LISTING_VISIBILITY_ONLY_START,
                Emotion::LISTING_VISIBILITY_ONLY_START_AND_LISTING,
            ], true);
        });
    }

    /**
     * @param array<string, mixed> $emotions
     */
    private function haveAllViewportsEmotions(array $emotions): bool
    {
        $devices = [];

        foreach ($emotions as $emotion) {
            $devices = array_merge($devices, $emotion['devicesArray']);
        }

        $devices = array_unique($devices);

        return \count($devices) === 5;
    }
}
