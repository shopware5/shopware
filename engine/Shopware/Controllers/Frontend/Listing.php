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
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * Listing controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Listing extends Enlight_Controller_Action
{
    /**
     * Listing of all manufacturer products.
     * Templates extends from the normal listing template.
     */
    public function manufacturerAction()
    {
        $manufacturerId = $this->Request()->getParam('sSupplier', null);

        /**@var $context ProductContextInterface*/
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        if (!$this->Request()->getParam('sCategory')) {
            $this->Request()->setParam('sCategory', $context->getShop()->getCategory()->getId());
        }

        /**@var $criteria Criteria*/
        $criteria = $this->get('shopware_search.store_front_criteria_factory')
            ->createListingCriteria($this->Request(), $context);

        if ($criteria->hasCondition('manufacturer')) {
            $condition = $criteria->getCondition('manufacturer');
            $criteria->removeCondition('manufacturer');
            $criteria->addBaseCondition($condition);
        }

        $categoryArticles = Shopware()->Modules()->Articles()->sGetArticlesByCategory(
            $context->getShop()->getCategory()->getId(),
            $criteria
        );

        /**@var $manufacturer Manufacturer*/
        $manufacturer = $this->get('shopware_storefront.manufacturer_service')->get(
            $manufacturerId,
            $this->get('shopware_storefront.context_service')->getShopContext()
        );

        $facets = array();
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
            'sCategory' => $context->getShop()->getCategory()->getId()
        ]);

        $this->View()->assign('sCategoryContent', $this->getSeoDataOfManufacturer($manufacturer));
    }

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

        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($requestCategoryId);

        $categoryId = $categoryContent['id'];
        Shopware()->System()->_GET['sCategory'] = $categoryId;

        $emotionConfiguration = $this->getEmotionConfiguration($categoryId);

        $location = $this->getRedirectLocation($categoryContent, $emotionConfiguration['hasEmotion']);
        if ($location) {
            return $this->redirect($location, array('code' => 301));
        }

        //check for seo information about the current manufacturer
        $seoSupplier = $this->get('config')->get('seoSupplier');
        $manufacturerId = $this->Request()->getParam('sSupplier', false);

        //old manufacturer listing
        if ($seoSupplier === true && $categoryContent['parentId'] == 1 && $manufacturerId) {

            /**@var $manufacturer Manufacturer*/
            $manufacturer = $this->get('shopware_storefront.manufacturer_service')->get(
                $manufacturerId,
                $this->get('shopware_storefront.context_service')->getShopContext()
            );
            
            if ($manufacturer === null) {
                throw new Enlight_Controller_Exception(
                    'Manufacturer missing, non-existent or invalid',
                    404
                );
            }

            $manufacturerContent = $this->getSeoDataOfManufacturer($manufacturer);

            $categoryContent = array_merge($categoryContent, $manufacturerContent);
        } elseif (!$requestCategoryId) {
            throw new Enlight_Controller_Exception(
                'Listing category missing, non-existent or invalid for the current shop',
                404
            );
        }

        $viewAssignments = array(
            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($categoryId),
            'sBreadcrumb' => $this->getBreadcrumb($categoryId),
            'sCategoryContent' => $categoryContent,
            'activeFilterGroup' => $this->request->getQuery('sFilterGroup'),
            'hasEscapedFragment' => $this->Request()->has('_escaped_fragment_'),
            'ajaxCountUrlParams' => ['sCategory' => $categoryContent['id']]
        );

        $viewAssignments = array_merge($viewAssignments, $emotionConfiguration);

        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        if ($categoryContent['streamId']) {
            /** @var \Shopware\Components\ProductStream\CriteriaFactoryInterface $factory */
            $factory = $this->get('shopware_product_stream.criteria_factory');
            $criteria = $factory->createCriteria($this->Request(), $context);

            /** @var \Shopware\Components\ProductStream\RepositoryInterface $streamRepository */
            $streamRepository = $this->get('shopware_product_stream.repository');
            $streamRepository->prepareCriteria($criteria, $categoryContent['streamId']);

            /** @var \Shopware\Components\ProductStream\FacetFilter $facetFilter */
            $facetFilter = $this->get('shopware_product_stream.facet_filter');
            $facetFilter->add($criteria);
        } else {
            /**@var $criteria Criteria*/
            $criteria = $this->get('shopware_search.store_front_criteria_factory')
                ->createListingCriteria($this->Request(), $context);
        }

        if ($categoryContent['hideFilter']) {
            $criteria->resetFacets();
        }

        if ($this->Request()->getParam('action') == 'manufacturer' && $criteria->hasCondition('manufacturer')) {
            $condition = $criteria->getCondition('manufacturer');
            $criteria->removeCondition('manufacturer');
            $criteria->addBaseCondition($condition);
        }

        $categoryArticles = Shopware()->Modules()->Articles()->sGetArticlesByCategory(
            $categoryId,
            $criteria
        );

        if ($this->Request()->getParam('sRss') || $this->Request()->getParam('sAtom')) {
            $this->Response()->setHeader('Content-Type', 'text/xml');
            $type = $this->Request()->getParam('sRss') ? 'rss' : 'atom';
            $this->View()->loadTemplate('frontend/listing/' . $type . '.tpl');
        } elseif (!empty($categoryContent['template'])) {
            if ($this->View()->templateExists('frontend/listing/' . $categoryContent['template'])) {
                $this->View()->loadTemplate('frontend/listing/' . $categoryContent['template']);
            } else {
                $this->get('corelogger')->error(
                    'Missing category template detected. Please correct the template for category "'.$categoryContent['name'].'".',
                    [
                        'uri' => $this->Request()->getRequestUri(),
                        'categoryId' => $requestCategoryId,
                        'categoryName' => $categoryContent['name']
                    ]
                );
            }
        }

        $viewAssignments['sCategoryContent'] = $categoryContent;

        /** @var \Shopware\Components\ProductStream\FacetFilter $facetFilter */
        $facetFilter = $this->get('shopware_product_stream.facet_filter');
        $facets = $facetFilter->filter($categoryArticles['facets'], $criteria);
        $categoryArticles['facets'] = $facets;

        $this->View()->assign($viewAssignments);
        $this->View()->assign($categoryArticles);
    }

    /**
     * @param array $categoryContent
     * @param bool $hasEmotion
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
            throw new \Enlight_Controller_Exception(
                'Category not found',
                Enlight_Controller_Exception::Controller_Dispatcher_Controller_Not_Found
            );
        } elseif ($this->isShopsBaseCategoryPage($categoryContent['id'])) {
            $location = array('controller' => 'index');
        } elseif ($this->get('config')->get('categoryDetailLink') && $checkRedirect) {
            /**@var $context ShopContextInterface*/
            $context = $this->get('shopware_storefront.context_service')->getShopContext();

            /**@var $factory StoreFrontCriteriaFactoryInterface*/
            $factory = $this->get('shopware_search.store_front_criteria_factory');
            $criteria = $factory->createListingCriteria($this->Request(), $context);

            $criteria->resetFacets()
                ->resetConditions()
                ->resetSorting()
                ->offset(0)
                ->limit(2)
                ->setFetchCount(false);

            /**@var $result ProductNumberSearchResult*/
            $result = $this->get('shopware_search.product_number_search')->search($criteria, $context);

            if (count($result->getProducts()) === 1) {
                /**@var $first \Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct*/
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
     * @return array
     */
    private function getSeoDataOfManufacturer(Manufacturer $manufacturer)
    {
        $content = array();

        $content['metaDescription'] = $manufacturer->getMetaDescription();
        $content['metaKeywords'] = $manufacturer->getMetaKeywords();

        $canonicalParams = array(
            'sViewport' => 'listing',
            'sAction'   => 'manufacturer',
            'sSupplier' => $manufacturer->getId(),
        );

        $content['canonicalParams'] = $canonicalParams;

        $path = $this->Front()->Router()->assemble($canonicalParams);

        if ($path) {
            /** @deprecated */
            $content['sSelfCanonical'] = $path;
        }

        $content['metaTitle'] = $manufacturer->getMetaTitle();
        $content['title'] = $manufacturer->getName();

        return $content;
    }

    /**
     * Returns a single emotion definition for the provided category id.
     *
     * @param $categoryId
     * @return array|mixed
     */
    private function getCategoryEmotion($categoryId)
    {
        if ($this->Request()->getQuery('sSupplier')
            || $this->Request()->getQuery('sPage')
            || $this->Request()->getQuery('sFilterProperties')
            || $this->Request()->getParam('sRss')
            || $this->Request()->getParam('sAtom')
        ) {
            return array();
        }

        $data = Shopware()->Models()->getRepository('Shopware\Models\Emotion\Emotion')
            ->getCategoryBaseEmotionsQuery($categoryId)->getArrayResult();

        if (empty($data)) {
            return array();
        }

        return array(
            'id' => $data[0]['id'],
            'showListing' => $data[0]['showListing']
        );
    }

    /**
     * Helper function which checks the configuration for listing filters.
     * @return boolean
     */
    protected function displayFiltersInListing()
    {
        return Shopware()->Config()->get('displayFiltersInListings', true);
    }

    /**
     * Returns listing breadcrumb
     *
     * @param int $categoryId
     * @return array
     */
    public function getBreadcrumb($categoryId)
    {
        $breadcrumb = Shopware()->Modules()->Categories()->sGetCategoriesByParent($categoryId);
        return array_reverse($breadcrumb);
    }

    /**
     * Checks if the provided $categoryId is in the current shop's category tree
     *
     * @param int $categoryId
     * @return bool
     */
    private function isValidCategoryPath($categoryId)
    {
        $defaultShopCategoryId = Shopware()->Shop()->getCategory()->getId();

        /**@var $repository \Shopware\Models\Category\Repository*/
        $categoryRepository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
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
     * @return bool
     */
    private function isShopsBaseCategoryPage($categoryId)
    {
        $defaultShopCategoryId = Shopware()->Shop()->getCategory()->getId();

        $queryParamsWhiteList = array('controller', 'action', 'sCategory', 'sViewport', 'rewriteUrl', 'module');
        $queryParamsNames = array_keys($this->Request()->getParams());
        $paramsDiff = array_diff($queryParamsNames, $queryParamsWhiteList);

        return ($defaultShopCategoryId == $categoryId && !$paramsDiff);
    }

    /**
     * @param int $templateVersion
     * @param int $categoryId
     * @return array
     */
    protected function getEmotionConfiguration($categoryId)
    {
        if ($this->Request()->getParam('sPage')) {
            return [
                'hasEmotion'  => false,
                'showListing' => true
            ];
        }

        $emotions = $this->get('emotion_device_configuration')->get($categoryId);

        return [
            'emotions' => $emotions,
            'hasEmotion' => !empty($emotions),
            'showListing' => empty($emotions) || (bool)max(array_column($emotions, 'showListing'))
        ];
    }
}
