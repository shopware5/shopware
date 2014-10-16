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

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_RequestHttp as Request;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\CustomerGroupCondition;
use Shopware\Bundle\SearchBundle\Condition\HasPriceCondition;
use Shopware\Bundle\SearchBundle\Condition\ImmediateDeliveryCondition;
use Shopware\Bundle\SearchBundle\Condition\ManufacturerCondition;
use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Condition\PropertyCondition;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\Condition\ShippingFreeCondition;
use Shopware\Bundle\SearchBundle\Condition\VoteAverageCondition;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ProductAttributeSorting;
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\Sorting\SearchRankingSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CriteriaFactory
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
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    /**
     * @param \Shopware_Components_Config $config
     * @param Connection $connection
     * @param \Enlight_Event_EventManager $eventManager
     * @param QueryAliasMapper $queryAliasMapper
     */
    public function __construct(
        \Shopware_Components_Config $config,
        Connection $connection,
        \Enlight_Event_EventManager $eventManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->config = $config;
        $this->connection = $connection;
        $this->eventManager = $eventManager;
        $this->queryAliasMapper = $queryAliasMapper;
    }

    /**
     * Maps the provided request parameters into a criteria object.
     *
     * @param Request $request
     * @param ShopContextInterface $context
     * @return Criteria
     */
    public function createCriteriaFromRequest(Request $request, ShopContextInterface $context)
    {
        $this->queryAliasMapper->replaceShortRequestQueries($request);

        $criteriaContext = new CriteriaContext();

        $criteriaContext->setLimit(
            $request->getParam('sPerPage', (int) $this->config->get('articlesPerPage'))
        );

        $page = $request->getParam('sPage', 1);
        $criteriaContext->setOffset(
            ($page - 1) * $criteriaContext->getLimit()
        );

        $defaultSort = $this->config->get('defaultListingSorting');

        $criteriaContext->setSort(
            $request->getParam('sSort', $defaultSort)
        );

        $criteriaContext->setMinPrice(
            $request->getParam('priceMin', null)
        );

        $criteriaContext->setMaxPrice(
            $request->getParam('priceMax', null)
        );

        $criteriaContext->setManufacturers(
            explode('|', $request->getParam('sSupplier', array()))
        );

        $criteriaContext->setShippingFree(
            $request->getParam('shippingFree', null)
        );

        $criteriaContext->setVoteAverage(
            $request->getParam('rating', null)
        );

        $criteriaContext->setImmediateDelivery(
            $request->getParam('immediateDelivery', null)
        );

        $criteriaContext->setCategory(
            $request->getParam('sCategory', null)
        );

        $search = $request->getParam('sSearch', null);

        if ($search !== null) {
            $search = trim(strip_tags(htmlspecialchars_decode(stripslashes($search))));

            //we have to strip the / otherwise broken urls would be created e.g. wrong pager urls
            $search = str_replace("/", "", $search);

            $criteriaContext->setSearch($search);
        }

        $properties = $request->getParam('sFilterProperties', array());
        $criteriaContext->setFilters(
            explode('|', $properties)
        );

        return $this->createCriteria($criteriaContext, $context);
    }

    /**
     * @param CriteriaContext $criteriaContext
     * @param ShopContextInterface $context
     * @return Criteria
     */
    private function createCriteria(CriteriaContext $criteriaContext, ShopContextInterface $context)
    {
        $criteria = new Criteria();

        $criteria->offset($criteriaContext->getOffset())
            ->limit($criteriaContext->getLimit());

        $criteria->addCondition(
            $this->createCustomerGroupCondition(
                array($context->getCurrentCustomerGroup()->getId())
            )
        );

        $criteria->addBaseCondition(
            $this->createHasPriceCondition()
        );

        if ($criteriaContext->getCategory()) {
            $criteria->addBaseCondition(
                $this->createCategoryCondition(
                    array($criteriaContext->getCategory())
                )
            );
        }

        if ($criteriaContext->getManufacturers()) {
            $criteria->addCondition(
                $this->createManufacturerCondition($criteriaContext->getManufacturers())
            );
        }

        if ($criteriaContext->getShippingFree()) {
            $criteria->addCondition(
                $this->createShippingFreeCondition()
            );
        }

        if ($criteriaContext->getImmediateDelivery()) {
            $criteria->addCondition(
                $this->createImmediateDeliveryCondition()
            );
        }

        if ($criteriaContext->getVoteAverage()) {
            $criteria->addCondition(
                $this->createVoteAverageCondition(
                    $criteriaContext->getVoteAverage()
                )
            );
        }

        if ($criteriaContext->getMinPrice() || $criteriaContext->getMaxPrice()) {
            $criteria->addCondition(
                $this->createPriceCondition(
                    (float) $criteriaContext->getMinPrice(),
                    (float) $criteriaContext->getMaxPrice()
                )
            );
        }

        if ($criteriaContext->getFilters()) {
            $filters = $this->getGroupedFilters(
                $criteriaContext->getFilters()
            );

            foreach ($filters as $filter) {
                $criteria->addCondition(
                    $this->createPropertyCondition($filter)
                );
            }
        }

        if ($criteriaContext->getSearch()) {
            $criteria->addBaseCondition(
                $this->createSearchTermCondition(
                    $criteriaContext->getSearch()
                )
            );
        }

        switch ($criteriaContext->getSort()) {
            case self::SORTING_RELEASE_DATE:
                $criteria->addSorting(
                    $this->createReleaseDateSorting(SortingInterface::SORT_DESC)
                );
                break;
            case self::SORTING_POPULARITY:
                $criteria->addSorting(
                    $this->createPopularitySorting(SortingInterface::SORT_DESC)
                );
                break;
            case self::SORTING_CHEAPEST_PRICE:
                $criteria->addSorting(
                    $this->createPriceSorting(SortingInterface::SORT_ASC)
                );
                break;
            case self::SORTING_HIGHEST_PRICE:
                $criteria->addSorting(
                    $this->createPriceSorting(SortingInterface::SORT_DESC)
                );
                break;
            case self::SORTING_PRODUCT_NAME_ASC:
                $criteria->addSorting(
                    $this->createProductNameSorting(SortingInterface::SORT_ASC)
                );
                break;
            case self::SORTING_PRODUCT_NAME_DESC:
                $criteria->addSorting(
                    $this->createProductNameSorting(SortingInterface::SORT_DESC)
                );
                break;
            case self::SORTING_SEARCH_RANKING:
                $criteria->addSorting(
                    $this->createSearchRankingSorting(SortingInterface::SORT_DESC)
                );
                break;
        }

        return $criteria;
    }

    /**
     * Helper function which groups the passed filter option ids
     * by the filter group.
     * Each filter group is joined as own PropertyCondition to the criteria
     * object
     *
     * @param $filters
     * @return array
     */
    private function getGroupedFilters($filters)
    {
        $sql = "
            SELECT
                optionID,
                GROUP_CONCAT(filterValues.id SEPARATOR '|') as valueIds
            FROM s_filter_values filterValues
            WHERE filterValues.id IN (?)
            GROUP BY filterValues.optionID
        ";

        $data = $this->connection->fetchAll(
            $sql,
            array($filters),
            array(Connection::PARAM_INT_ARRAY)
        );

        $result = array();
        foreach ($data as $value) {
            $optionId = $value['optionID'];
            $valueIds = explode('|', $value['valueIds']);
            if (empty($valueIds)) {
                continue;
            }
            $result[$optionId] = $valueIds;
        }

        return $result;
    }

    /**
     * @param string $direction
     * @return ReleaseDateSorting
     */
    public function createReleaseDateSorting($direction = SortingInterface::SORT_ASC)
    {
        return new ReleaseDateSorting($direction);
    }

    /**
     * @param string $direction
     * @return PopularitySorting
     */
    public function createPopularitySorting($direction = SortingInterface::SORT_DESC)
    {
        return new PopularitySorting($direction);
    }

    /**
     * @param string $direction
     * @return PriceSorting
     */
    public function createPriceSorting($direction = SortingInterface::SORT_ASC)
    {
        return new PriceSorting($direction);
    }

    /**
     * @param $field
     * @param string $direction
     * @return ProductAttributeSorting
     */
    public function createProductAttributeSorting($field, $direction = SortingInterface::SORT_ASC)
    {
        return new ProductAttributeSorting($field, $direction);
    }

    /**
     * @param string $direction
     * @return ProductNameSorting
     */
    public function createProductNameSorting($direction = SortingInterface::SORT_ASC)
    {
        return new ProductNameSorting($direction);
    }

    /**
     * @param string $direction
     * @return SearchRankingSorting
     */
    public function createSearchRankingSorting($direction = SortingInterface::SORT_DESC)
    {
        return new SearchRankingSorting($direction);
    }

    /**
     * @param $term
     * @return SearchTermCondition
     */
    public function createSearchTermCondition($term)
    {
        return new SearchTermCondition($term);
    }

    /**
     * @param $valueIds
     * @return PropertyCondition
     */
    public function createPropertyCondition($valueIds)
    {
        return new PropertyCondition($valueIds);
    }

    /**
     * @param $categoryIds
     * @return CategoryCondition
     */
    public function createCategoryCondition($categoryIds)
    {
        return new CategoryCondition($categoryIds);
    }

    /**
     * @param $manufacturerIds
     * @return ManufacturerCondition
     */
    public function createManufacturerCondition($manufacturerIds)
    {
        return new ManufacturerCondition($manufacturerIds);
    }

    /**
     * @param $customerGroupIds
     * @return CustomerGroupCondition
     */
    public function createCustomerGroupCondition($customerGroupIds)
    {
        return new CustomerGroupCondition($customerGroupIds);
    }

    /**
     * @return HasPriceCondition
     */
    public function createHasPriceCondition()
    {
        return new HasPriceCondition();
    }

    /**
     * @param $min
     * @param $max
     * @return PriceCondition
     */
    public function createPriceCondition($min, $max)
    {
        return new PriceCondition($min, $max);
    }

    /**
     * @param $average
     * @return VoteAverageCondition
     */
    public function createVoteAverageCondition($average)
    {
        return new VoteAverageCondition($average);
    }

    /**
     * @return ImmediateDeliveryCondition
     */
    public function createImmediateDeliveryCondition()
    {
        return new ImmediateDeliveryCondition();
    }

    /**
     * @return ShippingFreeCondition
     */
    public function createShippingFreeCondition()
    {
        return new ShippingFreeCondition();
    }

    /**
     * @param $field
     * @param $operator
     * @param $value
     * @return ProductAttributeCondition
     */
    public function createProductAttributeCondition($field, $operator, $value)
    {
        return new ProductAttributeCondition($field, $operator, $value);
    }
}
