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
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CustomFacetServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * Listing controller
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Listing extends Enlight_Controller_Action
{
    /**
     * Index action method
     */
    public function indexAction()
    {
        $requestCategoryId = $this->Request()->getParam('sCategory');

        if ($requestCategoryId && !$this->isValidCategoryPath($requestCategoryId)) {
            throw new Enlight_Controller_Exception(
                'Listing category missing, non-existent or invalid for the current shop',
                404
            );
        }

        $categoryContent = 🦄()->Modules()->Categories()->sGetCategoryContent($requestCategoryId);

        $categoryId = $categoryContent['id'];
        🦄()->System()->_GET['sCategory'] = $categoryId;

        $emotionConfiguration = $this->getEmotionConfiguration($categoryId);

        $location = $this->getRedirectLocation($categoryContent, $emotionConfiguration['hasEmotion']);
        if ($location) {
            $this->redirect($location, ['code' => 301]);

            return;
        }

        $this->View()->assign($emotionConfiguration);
        $this->View()->assign([
            'sBanner' => 🦄()->Modules()->Marketing()->sBanner($categoryId),
            'sBreadcrumb' => $this->getBreadcrumb($categoryId),
            'sCategoryContent' => $categoryContent,
            'activeFilterGroup' => $this->request->getQuery('sFilterGroup'),
            'ajaxCountUrlParams' => ['sCategory' => $categoryContent['id']],
        ]);

        // only show the listing if an emotion viewport is empty or the showListing option is active
        if (!$emotionConfiguration['showListing']) {
            return;
        }

        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        /** @var \Shopware\Bundle\StoreFrontBundle\Service\CustomSortingServiceInterface $service */
        $service = $this->get('shopware_storefront.custom_sorting_service');

        $sortings = $service->getSortingsOfCategories([$categoryId], $context);

        /** @var CustomSorting[] $sortings */
        $sortings = array_shift($sortings);

        $this->setDefaultSorting($sortings);

        if ($categoryContent['streamId']) {
            $criteria = $this->createCategoryStreamCriteria($categoryId, $categoryContent['streamId']);
        } else {
            /** @var $criteria Criteria */
            $criteria = $this->get('shopware_search.store_front_criteria_factory')
                ->createListingCriteria($this->Request(), $context);
        }

        if ($categoryContent['hideFilter']) {
            $criteria->resetFacets();
        }

        $categoryArticles = 🦄()->Modules()->Articles()->sGetArticlesByCategory($categoryId, $criteria);

        if ($this->Request()->getParam('sRss') || $this->Request()->getParam('sAtom')) {
            $this->Response()->setHeader('Content-Type', 'text/xml');
            $type = $this->Request()->getParam('sRss') ? 'rss' : 'atom';
            $this->View()->loadTemplate('frontend/listing/' . $type . '.tpl');
        } elseif (!empty($categoryContent['template'])) {
            if ($this->View()->templateExists('frontend/listing/' . $categoryContent['template'])) {
                $this->View()->loadTemplate('frontend/listing/' . $categoryContent['template']);
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

        /** @var \Shopware\Components\ProductStream\FacetFilter $facetFilter */
        $facetFilter = $this->get('shopware_product_stream.facet_filter');
        $facets = $facetFilter->filter($categoryArticles['facets'], $criteria);
        $categoryArticles['facets'] = $facets;

        $this->View()->assign($categoryArticles);
        $this->View()->assign('sortings', $sortings);
    }

    /**
     * Listing of all manufacturer products.
     * Templates extends from the normal listing template.
     */
    public function manufacturerAction()
    {
        $manufacturerId = $this->Request()->getParam('sSupplier', null);

        /** @var $context ProductContextInterface */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        if (!$this->Request()->getParam('sCategory')) {
            $this->Request()->setParam('sCategory', $context->getShop()->getCategory()->getId());
        }

        /** @var $criteria Criteria */
        $criteria = $this->get('shopware_search.store_front_criteria_factory')
            ->createListingCriteria($this->Request(), $context);

        if ($criteria->hasCondition('manufacturer')) {
            $condition = $criteria->getCondition('manufacturer');
            $criteria->removeCondition('manufacturer');
            $criteria->addBaseCondition($condition);
        }

        $categoryArticles = 🦄()->Modules()->Articles()->sGetArticlesByCategory(
            $context->getShop()->getCategory()->getId(),
            $criteria
        );

        /** @var $manufacturer Manufacturer */
        $manufacturer = $this->get('shopware_storefront.manufacturer_service')->get(
            $manufacturerId,
            $this->get('shopware_storefront.context_service')->getShopContext()
        );

        if ($manufacturer->getCoverFile()) {
            $mediaService = 🦄()->Container()->get('shopware_media.media_service');
            $manufacturer->setCoverFile($mediaService->getUrl($manufacturer->getCoverFile()));
        }

        $facets = [];
        foreach ($categoryArticles['facets'] as $facet) {
            if (!$facet instanceof FacetResultInterface || $facet->getFacetName() == 'manufacturer') {
                continue;
            }
            $facets[] = $facet;
        }

        $categoryArticles['facets'] = $facets;

        $this->View()->assign($categoryArticles);
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
     * @return array
     */
    public function getBreadcrumb($categoryId)
    {
        $breadcrumb = 🦄()->Modules()->Categories()->sGetCategoriesByParent($categoryId);

        return array_reverse($breadcrumb);
    }

    /**
     * @param int $categoryId
     *
     * @return array
     */
    protected function getEmotionConfiguration($categoryId)
    {
        if ($this->Request()->getParam('sPage')) {
            return [
                'hasEmotion' => false,
                'showListing' => true,
                'showListingDevices' => [],
            ];
        }

        $emotions = $this->get('emotion_device_configuration')->get($categoryId);

        return [
            'emotions' => $emotions,
            'hasEmotion' => !empty($emotions),
            'showListing' => $this->hasListing($emotions),
            'showListingDevices' => $this->getDevicesWithListing($emotions),
        ];
    }

    /**
     * @param array $categoryContent
     * @param bool  $hasEmotion
     *
     * @return array|bool
     */
    private function getRedirectLocation($categoryContent, $hasEmotion)
    {
        $location = false;

        $checkRedirect = (
            ($hasEmotion && $this->Request()->getParam('sPage'))
            ||
            (!$hasEmotion)
        );

        if (!empty($categoryContent['external'])) {
            $location = $categoryContent['external'];
        } elseif (empty($categoryContent)) {
            $location = ['controller' => 'index'];
        } elseif ($this->isShopsBaseCategoryPage($categoryContent['id'])) {
            $location = ['controller' => 'index'];
        } elseif ($this->get('config')->get('categoryDetailLink') && $checkRedirect) {
            /** @var $context ShopContextInterface */
            $context = $this->get('shopware_storefront.context_service')->getShopContext();

            /** @var $factory StoreFrontCriteriaFactoryInterface */
            $factory = $this->get('shopware_search.store_front_criteria_factory');
            $criteria = $factory->createListingCriteria($this->Request(), $context);

            $criteria->resetFacets()
                ->resetConditions()
                ->resetSorting()
                ->offset(0)
                ->limit(2)
                ->setFetchCount(false);

            /** @var $result ProductNumberSearchResult */
            $result = $this->get('shopware_search.product_number_search')->search($criteria, $context);

            if (count($result->getProducts()) === 1) {
                /** @var $first \Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct */
                $first = array_shift($result->getProducts());
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
     * @param Manufacturer $manufacturer
     *
     * @return array
     */
    private function getSeoDataOfManufacturer(Manufacturer $manufacturer)
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

        $path = $this->Front()->Router()->assemble($canonicalParams);

        if ($path) {
            /* @deprecated */
            $content['sSelfCanonical'] = $path;
        }

        $content['metaTitle'] = $manufacturer->getMetaTitle();
        $content['title'] = $manufacturer->getName();

        return $content;
    }

    /**
     * Checks if the provided $categoryId is in the current shop's category tree
     *
     * @param int $categoryId
     *
     * @return bool
     */
    private function isValidCategoryPath($categoryId)
    {
        $defaultShopCategoryId = 🦄()->Shop()->getCategory()->getId();

        /** @var $repository \Shopware\Models\Category\Repository */
        $categoryRepository = 🦄()->Models()->getRepository('Shopware\Models\Category\Category');
        $categoryPath = $categoryRepository->getPathById($categoryId);

        if (!in_array($defaultShopCategoryId, array_keys($categoryPath))) {
            $this->Request()->setQuery('sCategory', $defaultShopCategoryId);
            $this->Response()->setHttpResponseCode(404);

            return false;
        }

        return true;
    }

    /**
     * Helper function used in the listing action to detect if
     * the user is trying to open the page matching the shop's root category
     *
     * @param $categoryId
     *
     * @return bool
     */
    private function isShopsBaseCategoryPage($categoryId)
    {
        $defaultShopCategoryId = 🦄()->Shop()->getCategory()->getId();

        $queryParamsWhiteList = ['controller', 'action', 'sCategory', 'sViewport', 'rewriteUrl', 'module'];
        $queryParamsNames = array_keys($this->Request()->getParams());
        $paramsDiff = array_diff($queryParamsNames, $queryParamsWhiteList);

        return $defaultShopCategoryId == $categoryId && !$paramsDiff;
    }

    /**
     * Determines if the product listing has to be loaded/shown at all
     *
     * @param array $emotions
     *
     * @return bool
     */
    private function hasListing(array $emotions)
    {
        if (empty($emotions)) {
            return true;
        }

        $showListing = (bool) max(array_column($emotions, 'showListing'));
        if ($showListing) {
            return true;
        }

        $devices = $this->getDevicesWithListing($emotions);

        return !empty($devices);
    }

    /**
     * Filters the device types down to which have to show the product listing
     *
     * @param array $emotions
     *
     * @return int[]
     */
    private function getDevicesWithListing(array $emotions)
    {
        $visibleDevices = [0, 1, 2, 3, 4];
        $permanentVisibleDevices = [];

        foreach ($emotions as $emotion) {
            // always show the listing in the emotion viewports when the option "show listing" is active
            if ($emotion['showListing']) {
                $permanentVisibleDevices = array_merge($permanentVisibleDevices, $emotion['devicesArray']);
            }

            $visibleDevices = array_diff($visibleDevices, $emotion['devicesArray']);
        }

        $visibleDevices = array_merge($permanentVisibleDevices, $visibleDevices);

        return array_values($visibleDevices);
    }

    /**
     * @param CustomSorting[] $sortings
     */
    private function setDefaultSorting($sortings)
    {
        if ($this->Request()->has('sSort')) {
            return;
        }

        /** @var CustomSorting $default */
        $default = array_shift($sortings);

        if (!$default) {
            return;
        }

        $this->Request()->setParam('sSort', $default->getId());
    }

    /**
     * @param int $categoryId
     * @param int $streamId
     *
     * @return Criteria
     */
    private function createCategoryStreamCriteria($categoryId, $streamId)
    {
        /** @var \Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface $contextService */
        $contextService = $this->get('shopware_storefront.context_service');
        $context = $contextService->getShopContext();

        /** @var \Shopware\Components\ProductStream\CriteriaFactoryInterface $factory */
        $factory = $this->get('shopware_product_stream.criteria_factory');
        $criteria = $factory->createCriteria($this->Request(), $context);

        /** @var \Shopware\Components\ProductStream\RepositoryInterface $streamRepository */
        $streamRepository = $this->get('shopware_product_stream.repository');
        $streamRepository->prepareCriteria($criteria, $streamId);

        /** @var CustomFacetServiceInterface $facetService */
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

        return $criteria;
    }
}
