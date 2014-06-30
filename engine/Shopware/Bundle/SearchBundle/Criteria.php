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

use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\SearchBundle\Sorting;

/**
 * The criteria object is used for the search gateway.
 *
 * The sorting, facet and condition classes are defined global and has
 * to be compatible with all gateway engines.
 *
 * Each of this sorting, facet and condition classes are handled by their
 * own handler classes which implemented for each gateway engine.
 *
 * @package Shopware\Bundle\SearchBundle
 */
class Criteria
{
    /**
     * Offset for the limitation
     * @var int
     */
    private $offset;

    /**
     * Count of result
     * @var int
     */
    private $limit;

    /**
     * @var ConditionInterface[]
     */
    private $conditions = array();

    /**
     * @var FacetInterface[]
     */
    private $facets = array();

    /**
     * @var SortingInterface[]
     */
    private $sortings = array();

    /**
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param array $categoryIds
     * @return $this
     */
    public function category(array $categoryIds)
    {
        return $this->addCondition(
            new Condition\CategoryCondition($categoryIds)
        );
    }

    /**
     * @param array $manufacturerIds
     * @return $this
     */
    public function manufacturer(array $manufacturerIds)
    {
        return $this->addCondition(
            new Condition\ManufacturerCondition($manufacturerIds)
        );
    }

    /**
     * @param $min
     * @param $max
     *
     * @return $this
     */
    public function price($min, $max)
    {
        return $this->addCondition(
            new Condition\PriceCondition($min, $max)
        );
    }

    /**
     * @param array $valueIds
     * @return $this
     */
    public function properties(array $valueIds)
    {
        return $this->addCondition(
            new Condition\PropertyCondition($valueIds)
        );
    }

    /**
     * @return $this
     */
    public function shippingFree()
    {
        return $this->addCondition(
            new Condition\ShippingFreeCondition()
        );
    }

    /**
     * @return $this
     */
    public function immediateDelivery()
    {
        return $this->addCondition(
            new Condition\ImmediateDeliveryCondition()
        );
    }

    /**
     * @param array $customerGroupIds
     * @return $this
     */
    public function customerGroup(array $customerGroupIds)
    {
        return $this->addCondition(
            new Condition\CustomerGroupCondition($customerGroupIds)
        );
    }

    /**
     * @return $this
     */
    public function manufacturerFacet()
    {
        return $this->addFacet(
            new Facet\ManufacturerFacet()
        );
    }

    /**
     * @return $this
     */
    public function categoryFacet()
    {
        return $this->addFacet(
            new Facet\CategoryFacet()
        );
    }

    public function shippingFreeFacet()
    {
        return $this->addFacet(
            new Facet\ShippingFreeFacet()
        );
    }

    /**
     * @return $this
     */
    public function priceFacet()
    {
        return $this->addFacet(
            new Facet\PriceFacet()
        );
    }

    /**
     * @return $this
     */
    public function immediateDeliveryFacet()
    {
        return $this->addFacet(
            new Facet\ImmediateDeliveryFacet()
        );
    }

    /**
     * @return $this
     */
    public function propertyFacet()
    {
        return $this->addFacet(
            new Facet\PropertyFacet()
        );
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function sortByReleaseDate($direction = 'ASC')
    {
        return $this->addSorting(
            new Sorting\ReleaseDateSorting($direction)
        );
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function sortByPopularity($direction = 'ASC')
    {
        return $this->addSorting(
            new Sorting\PopularitySorting($direction)
        );
    }

    /**
     * @return $this
     */
    public function sortByCheapestPrice()
    {
        return $this->addSorting(
            new Sorting\PriceSorting('ASC')
        );
    }

    /**
     * @return $this
     */
    public function sortByHighestPrice()
    {
        return $this->addSorting(
            new Sorting\PriceSorting('DESC')
        );
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function sortByDescription($direction = 'ASC')
    {
        return $this->addSorting(
            new Sorting\DescriptionSorting($direction)
        );
    }

    /**
     * @param FacetInterface $facet
     * @return $this
     */
    public function addFacet(FacetInterface $facet)
    {
        $this->facets[$facet->getName()] = $facet;
        return $this;
    }

    /**
     * @param ConditionInterface $condition
     * @return $this
     */
    public function addCondition(ConditionInterface $condition)
    {
        $this->conditions[$condition->getName()] = $condition;
        return $this;
    }

    /**
     * @param SortingInterface $sorting
     * @return $this
     */
    public function addSorting(SortingInterface $sorting)
    {
        $this->sortings[$sorting->getName()] = $sorting;
        return $this;
    }

    /**
     * @param $name
     * @return null|ConditionInterface
     */
    public function getCondition($name)
    {
        return $this->conditions[$name];
    }

    /**
     * @param $name
     * @return null|FacetInterface
     */
    public function getFacet($name)
    {
        return $this->facets[$name];
    }

    /**
     * @param $name
     * @return null|SortingInterface
     */
    public function getSorting($name)
    {
        return $this->sortings[$name];
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\ConditionInterface[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\FacetInterface[]
     */
    public function getFacets()
    {
        return $this->facets;
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\SortingInterface[]
     */
    public function getSortings()
    {
        return $this->sortings;
    }
}
