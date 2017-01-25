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

namespace Shopware\Bundle\SearchBundle\CriteriaRequestHandler;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_RequestHttp as Request;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\CustomerGroupCondition;
use Shopware\Bundle\SearchBundle\Condition\ImmediateDeliveryCondition;
use Shopware\Bundle\SearchBundle\Condition\IsAvailableCondition;
use Shopware\Bundle\SearchBundle\Condition\ManufacturerCondition;
use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\Condition\ShippingFreeCondition;
use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandlerInterface;
use Shopware\Bundle\SearchBundle\Facet\CategoryFacet;
use Shopware\Bundle\SearchBundle\Facet\ImmediateDeliveryFacet;
use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\Facet\ShippingFreeFacet;
use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\SearchBundle\SearchTermPreProcessorInterface;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\Sorting\SearchRankingSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @package Shopware\Bundle\SearchBundleDBAL\CriteriaRequestHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CoreCriteriaRequestHandler implements CriteriaRequestHandlerInterface
{
    const SORTING_RELEASE_DATE = 1;
    const SORTING_POPULARITY = 2;
    const SORTING_CHEAPEST_PRICE = 3;
    const SORTING_HIGHEST_PRICE = 4;
    const SORTING_PRODUCT_NAME_ASC = 5;
    const SORTING_PRODUCT_NAME_DESC = 6;
    const SORTING_SEARCH_RANKING = 7;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SearchTermPreProcessorInterface
     */
    private $searchTermPreProcessor;

    /**
     * @param \Shopware_Components_Config $config
     * @param Connection $connection
     * @param SearchTermPreProcessorInterface $searchTermPreProcessor
     */
    public function __construct(
        \Shopware_Components_Config $config,
        Connection $connection,
        SearchTermPreProcessorInterface $searchTermPreProcessor
    ) {
        $this->config = $config;
        $this->connection = $connection;
        $this->searchTermPreProcessor = $searchTermPreProcessor;
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     */
    public function handleRequest(Request $request, Criteria $criteria, ShopContextInterface $context)
    {
        $this->addLimit($request, $criteria);
        $this->addOffset($request, $criteria);

        $this->addCategoryCondition($request, $criteria);
        $this->addIsAvailableCondition($criteria);
        $this->addCustomerGroupCondition($criteria, $context);
        $this->addSearchCondition($request, $criteria);

        $this->addManufacturerCondition($request, $criteria);
        $this->addShippingFreeCondition($request, $criteria);
        $this->addImmediateDeliveryCondition($request, $criteria);
        $this->addRatingCondition($request, $criteria);
        $this->addPriceCondition($request, $criteria);

        $this->addSorting($request, $criteria);

        $this->addFacets($criteria);
    }

    /**
     * @param Criteria $criteria
     */
    private function addFacets(Criteria $criteria)
    {
        if ($this->config->get('showImmediateDeliveryFacet')) {
            $criteria->addFacet(new ImmediateDeliveryFacet());
        }

        if ($this->config->get('showShippingFreeFacet')) {
            $criteria->addFacet(new ShippingFreeFacet());
        }

        if ($this->config->get('showPriceFacet')) {
            $criteria->addFacet(new PriceFacet());
        }

        if ($this->config->get('showVoteAverageFacet')) {
            $criteria->addFacet(new VoteAverageFacet());
        }

        if ($this->config->get('showSupplierInCategories')) {
            $criteria->addFacet(new ManufacturerFacet());
        }

        $criteria->addFacet(new CategoryFacet());
    }


    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addCategoryCondition(Request $request, Criteria $criteria)
    {
        $category = $request->getParam('sCategory', null);
        if (!$category) {
            return;
        }

        $condition = new CategoryCondition([$category]);
        $criteria->addBaseCondition($condition);
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addManufacturerCondition(Request $request, Criteria $criteria)
    {
        if (!$request->has('sSupplier')) {
            return;
        }

        $manufacturers = explode(
            '|',
            $request->getParam('sSupplier')
        );

        if (!empty($manufacturers)) {
            $criteria->addCondition(new ManufacturerCondition($manufacturers));
        }
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addShippingFreeCondition(Request $request, Criteria $criteria)
    {
        $shippingFree = $request->getParam('shippingFree', null);
        if (!$shippingFree) {
            return;
        }

        $criteria->addCondition(new ShippingFreeCondition());
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addImmediateDeliveryCondition(Request $request, Criteria $criteria)
    {
        $immediateDelivery = $request->getParam('immediateDelivery', null);
        if (!$immediateDelivery) {
            return;
        }

        $criteria->addCondition(new ImmediateDeliveryCondition());
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addRatingCondition(Request $request, Criteria $criteria)
    {
        $average = $request->getParam('rating', null);
        if (!$average) {
            return;
        }

        $criteria->addCondition(new VoteAverageCondition($average));
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addPriceCondition(Request $request, Criteria $criteria)
    {
        $min = $request->getParam('priceMin', null);
        $max = $request->getParam('priceMax', null);

        if (!$min && !$max) {
            return;
        }

        $condition = new PriceCondition((float)$min, (float)$max);
        $criteria->addCondition($condition);
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addSearchCondition(Request $request, Criteria $criteria)
    {
        $term = $request->getParam('sSearch', null);
        if ($term == null) {
            return;
        }
        $term = $this->searchTermPreProcessor->process($term);
        $criteria->addBaseCondition(new SearchTermCondition($term));
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addSorting(Request $request, Criteria $criteria)
    {
        $defaultSort = $this->config->get('defaultListingSorting');
        $sort = $request->getParam('sSort', $defaultSort);

        switch ($sort) {
            case self::SORTING_RELEASE_DATE:
                $criteria->addSorting(
                    new ReleaseDateSorting(SortingInterface::SORT_DESC)
                );
                break;
            case self::SORTING_POPULARITY:
                $criteria->addSorting(
                    new PopularitySorting(SortingInterface::SORT_DESC)
                );
                break;
            case self::SORTING_CHEAPEST_PRICE:
                $criteria->addSorting(
                    new PriceSorting(SortingInterface::SORT_ASC)
                );
                break;
            case self::SORTING_HIGHEST_PRICE:
                $criteria->addSorting(
                    new PriceSorting(SortingInterface::SORT_DESC)
                );
                break;
            case self::SORTING_PRODUCT_NAME_ASC:
                $criteria->addSorting(
                    new ProductNameSorting(SortingInterface::SORT_ASC)
                );
                break;
            case self::SORTING_PRODUCT_NAME_DESC:
                $criteria->addSorting(
                    new ProductNameSorting(SortingInterface::SORT_DESC)
                );
                break;
            case self::SORTING_SEARCH_RANKING:
                $criteria->addSorting(
                    new SearchRankingSorting(SortingInterface::SORT_DESC)
                );
                break;
        }
    }

    /**
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     */
    private function addCustomerGroupCondition(Criteria $criteria, ShopContextInterface $context)
    {
        $condition = new CustomerGroupCondition(
            [$context->getCurrentCustomerGroup()->getId()]
        );
        $criteria->addBaseCondition($condition);
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addOffset(Request $request, Criteria $criteria)
    {
        $page = (int) $request->getParam('sPage', 1);
        $page = ($page > 0) ? $page : 1;
        $request->setParam('sPage', $page);

        $criteria->offset(
            ($page - 1) * $criteria->getLimit()
        );
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     */
    private function addLimit(Request $request, Criteria $criteria)
    {
        $limit = (int) $request->getParam('sPerPage', $this->config->get('articlesPerPage'));
        $max = $this->config->get('maxStoreFrontLimit', null);
        if ($max) {
            $limit = min($limit, $max);
        }
        $limit = $limit >= 1 ? $limit: 1;
        $criteria->limit($limit);
    }

    /**
     * @param Criteria $criteria
     */
    private function addIsAvailableCondition(Criteria $criteria)
    {
        if (!$this->config->get('hideNoInStock')) {
            return;
        }
        $criteria->addBaseCondition(new IsAvailableCondition());
    }
}
