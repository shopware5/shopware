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
        $this->conditions[] = new Condition\CategoryCondition($categoryIds);
        return $this;
    }

    /**
     * @param array $manufacturerIds
     * @return $this
     */
    public function manufacturer(array $manufacturerIds)
    {
        $this->conditions[] = new Condition\ManufacturerCondition($manufacturerIds);
        return $this;
    }

    /**
     * @param $min
     * @param $max
     *
     * @return $this
     */
    public function price($min, $max)
    {
        $this->conditions[] = new Condition\PriceCondition($min, $max);
        return $this;
    }

    /**
     * @param array $valueIds
     * @return $this
     */
    public function properties(array $valueIds)
    {
        $this->conditions[] = new Condition\PropertyCondition($valueIds);
        return $this;
    }

    /**
     * @return $this
     */
    public function shippingFree()
    {
        $this->conditions[] = new Condition\ShippingFreeCondition();
        return $this;
    }

    /**
     * @return $this
     */
    public function immediateDelivery()
    {
        $this->conditions[] = new Condition\ImmediateDeliveryCondition();
        return $this;
    }

    /**
     * @param array $customerGroupIds
     * @return $this
     */
    public function customerGroup(array $customerGroupIds)
    {
        $this->conditions[] = new Condition\CustomerGroupCondition($customerGroupIds);
        return $this;
    }

    /**
     * @return $this
     */
    public function manufacturerFacet()
    {
        $this->facets[] = new Facet\ManufacturerFacet();
        return $this;
    }

    /**
     * @return $this
     */
    public function categoryFacet()
    {
        $this->facets[] = new Facet\CategoryFacet();
        return $this;
    }

    public function shippingFreeFacet()
    {
        $this->facets[] = new Facet\ShippingFreeFacet();
        return $this;
    }

    /**
     * @return $this
     */
    public function priceFacet()
    {
        $this->facets[] = new Facet\PriceFacet();
        return $this;
    }

    /**
     * @return $this
     */
    public function immediateDeliveryFacet()
    {
        $this->facets[] = new Facet\ImmediateDeliveryFacet();
        return $this;
    }

    /**
     * @return $this
     */
    public function propertyFacet()
    {
        $this->facets[] = new Facet\PropertyFacet();
        return $this;
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function sortByReleaseDate($direction = 'ASC')
    {
        $this->sortings[] = new Sorting\ReleaseDateSorting($direction);
        return $this;
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function sortByPopularity($direction = 'ASC')
    {
        $this->sortings[] = new Sorting\PopularitySorting($direction);
        return $this;
    }

    /**
     * @return $this
     */
    public function sortByCheapestPrice()
    {
        $this->sortings[] = new Sorting\PriceSorting('ASC');
        return $this;
    }

    /**
     * @return $this
     */
    public function sortByHighestPrice()
    {
        $this->sortings[] = new Sorting\PriceSorting('DESC');
        return $this;
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function sortByDescription($direction = 'ASC')
    {
        $this->sortings[] = new Sorting\DescriptionSorting($direction);
        return $this;
    }

    /**
     * @param FacetInterface $facet
     * @return $this
     */
    public function addFacet(FacetInterface $facet)
    {
        $this->facets[] = $facet;
        return $this;
    }

    /**
     * @param ConditionInterface $condition
     * @return $this
     */
    public function addCondition(ConditionInterface $condition)
    {
        $this->conditions[] = $condition;
        return $this;
    }

    /**
     * @param SortingInterface $sorting
     * @return $this
     */
    public function addSorting(SortingInterface $sorting)
    {
        $this->sortings[] = $sorting;
        return $this;
    }

    /**
     * @param $name
     * @return null|ConditionInterface
     */
    public function getCondition($name)
    {
        foreach ($this->conditions as $condition) {
            if ($condition->getName() == $name) {
                return $condition;
            }
        }
        return null;
    }

    /**
     * @param $name
     * @return null|FacetInterface
     */
    public function getFacet($name)
    {
        foreach ($this->facets as $facet) {
            if ($facet->getName() == $name) {
                return $facet;
            }
        }
        return null;
    }

    /**
     * @param $name
     * @return null|SortingInterface
     */
    public function getSorting($name)
    {
        foreach ($this->sortings as $sorting) {
            if ($sorting->getName() == $name) {
                return $sorting;
            }
        }
        return null;
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
