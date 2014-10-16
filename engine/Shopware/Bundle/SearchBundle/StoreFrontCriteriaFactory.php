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

namespace Shopware\Bundle\SearchBundle;

use Enlight_Controller_Request_RequestHttp as Request;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Facet\ImmediateDeliveryFacet;
use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\Facet\PropertyFacet;
use Shopware\Bundle\SearchBundle\Facet\ShippingFreeFacet;
use Shopware\Bundle\SearchBundle\Facet\VoteAverageFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class StoreFrontCriteriaFactory
{
    /**
     * @var CriteriaFactory
     */
    private $criteriaFactory;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    /**
     * @param CriteriaFactory $criteriaFactory
     * @param \Shopware_Components_Config $config
     * @param QueryAliasMapper $queryAliasMapper
     */
    public function __construct(
        CriteriaFactory $criteriaFactory,
        \Shopware_Components_Config $config,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->criteriaFactory = $criteriaFactory;
        $this->config = $config;
        $this->queryAliasMapper = $queryAliasMapper;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createSearchCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->getSearchCriteria($request, $context);

        $this->addFacets($criteria);

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createAjaxSearchCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->getSearchCriteria($request, $context);

        $criteria->limit($this->config->get('MaxLiveSearchResults', 6));

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createListingCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->criteriaFactory->createCriteriaFromRequest($request, $context);

        $this->addFacets($criteria);

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @param int $categoryId
     * @return \Shopware\Bundle\SearchBundle\Criteria
     */
    public function createProductNavigationCriteria(
        Request $request,
        ShopContextInterface $context,
        $categoryId
    ) {
        $criteria = $this->criteriaFactory->createCriteriaFromRequest($request, $context);

        $criteria
            ->offset(0)
            ->limit(null);

        $criteria->removeCondition('category');
        $criteria->addBaseCondition(new CategoryCondition($categoryId));

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createAjaxListingCriteria(Request $request, ShopContextInterface $context)
    {
        return $this->criteriaFactory->createCriteriaFromRequest($request, $context);
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createAjaxCountCriteria(Request $request, ShopContextInterface $context)
    {
        $criteria = $this->criteriaFactory->createCriteriaFromRequest($request, $context);

        $criteria
            ->offset(0)
            ->limit(1)
            ->resetSorting();

        return $criteria;
    }

    /**
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    private function getSearchCriteria(Request $request, ShopContextInterface $context)
    {
        $this->queryAliasMapper->replaceShortRequestQueries($request);

        if (!$request->has('sSort')) {
            $request->setParam('sSort', CriteriaFactory::SORTING_SEARCH_RANKING);
        }

        $criteria = $this->criteriaFactory->createCriteriaFromRequest(
            $request,
            $context
        );

        if (!$criteria->hasCondition('category')) {
            $categoryId = $context->getShop()->getCategory()->getId();

            $criteria->addBaseCondition(
                new CategoryCondition(array($categoryId))
            );
        }

        $this->addFacets($criteria);

        return $criteria;
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

        if ($this->config->get('displayFiltersInListings')) {
            $criteria->addFacet(new PropertyFacet());
        }
    }
}
