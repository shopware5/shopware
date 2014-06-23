<?php

namespace Shopware\Gateway\Search;

use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Facet;
use Shopware\Gateway\Search\Sorting;
use Shopware\Struct\Customer\Group;

class Criteria
{
    public $query;

    public $offset;

    public $limit;

    /**
     * @var Condition[]
     */
    public $conditions = array();

    /**
     * @var Facet[]
     */
    public $facets = array();

    /**
     * @var Sorting[]
     */
    public $sortings = array();

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
     * @param $search
     * @return $this
     */
    public function query($search)
    {
        $this->query = $search;
        return $this;
    }

    /**
     * @param array $categoryIds
     * @return $this
     */
    public function category(array $categoryIds)
    {
        $this->conditions[] = new Condition\Category($categoryIds);
        return $this;
    }

    /**
     * @param array $manufacturerIds
     * @return $this
     */
    public function manufacturer(array $manufacturerIds)
    {
        $this->conditions[] = new Condition\Manufacturer($manufacturerIds);
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
        $this->conditions[] = new Condition\Price($min, $max);
        return $this;
    }

    /**
     * @param array $valueIds
     * @return $this
     */
    public function properties(array $valueIds)
    {
        $this->conditions[] = new Condition\Property($valueIds);
        return $this;
    }

    public function shippingFree()
    {
        $this->conditions[] = new Condition\ShippingFree();
        return $this;
    }

    /**
     * @param array $customerGroupIds
     * @return $this
     */
    public function customerGroup(array $customerGroupIds)
    {
        $this->conditions[] = new Condition\CustomerGroup($customerGroupIds);
        return $this;
    }

    /**
     * @return $this
     */
    public function manufacturerFacet()
    {
        $this->facets[] = new Facet\Manufacturer();
        return $this;
    }

    /**
     * @return $this
     */
    public function categoryFacet()
    {
        $this->facets[] = new Facet\Category();
        return $this;
    }

    public function shippingFreeFacet()
    {
        $this->facets[] = new Facet\ShippingFree();
        return $this;
    }

    /**
     * @return $this
     */
    public function priceFacet()
    {
        $this->facets[] = new Facet\Price();
        return $this;
    }

    /**
     * @return $this
     */
    public function propertyFacet()
    {
        $this->facets[] = new Facet\Property();
        return $this;
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function sortByReleaseDate($direction = 'ASC')
    {
        $this->sortings[] = new Sorting\ReleaseDate($direction);
        return $this;
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function sortByPopularity($direction = 'ASC')
    {
        $this->sortings[] = new Sorting\Popularity($direction);
        return $this;
    }

    /**
     * @return $this
     */
    public function sortByCheapestPrice()
    {
        $this->sortings[] = new Sorting\Price('ASC');
        return $this;
    }

    /**
     * @return $this
     */
    public function sortByHighestPrice()
    {
        $this->sortings[] = new Sorting\Price('DESC');
        return $this;
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function sortByDescription($direction = 'ASC')
    {
        $this->sortings[] = new Sorting\Description($direction);
        return $this;
    }

    /**
     * @param Facet $facet
     * @return $this
     */
    public function addFacet(Facet $facet)
    {
        $this->facets[] = $facet;
        return $this;
    }

    /**
     * @param Condition $condition
     * @return $this
     */
    public function addCondition(Condition $condition)
    {
        $this->conditions[] = $condition;
        return $this;
    }

    /**
     * @param Sorting $sorting
     * @return $this
     */
    public function addSorting(Sorting $sorting)
    {
        $this->sortings[] = $sorting;
        return $this;
    }

    /**
     * @param $name
     * @return null|Condition
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
     * @return null|Facet
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
     * @return null|Sorting
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
}
