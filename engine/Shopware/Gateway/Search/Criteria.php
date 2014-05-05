<?php

namespace Shopware\Gateway\Search;

use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Facet;
use Shopware\Gateway\Search\Sorting;

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
     * @param $id
     * @return $this
     */
    public function category($id)
    {
        $this->conditions[] = new Condition\Category($id);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function manufacturer($id)
    {
        $this->conditions[] = new Condition\Manufacturer($id);
        return $this;
    }

    /**
     * @param $min
     * @param $max
     * @param $customerGroupKey
     * @return $this
     */
    public function price($min, $max, $customerGroupKey)
    {
        $this->conditions[] = new Condition\Price($min, $max, $customerGroupKey);
        return $this;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function properties(array $values)
    {
        $this->conditions[] = new Condition\Property($values);
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function customerGroup($key)
    {
        $this->conditions[] = new Condition\CustomerGroup($key);
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

    /**
     * @param $customerGroupKey
     * @return $this
     */
    public function priceFacet($customerGroupKey)
    {
        $this->facets[] = new Facet\Price($customerGroupKey);
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
     * @param $customerGroupKey
     * @param string $direction
     * @return $this
     */
    public function sortByPrice($customerGroupKey, $direction = 'ASC')
    {
        $this->sortings[] = new Sorting\Price($direction, $customerGroupKey);
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