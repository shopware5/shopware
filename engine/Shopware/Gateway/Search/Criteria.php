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

    public function category($id)
    {
        $this->conditions[] = new Condition\Category($id);
    }

    public function manufacturer($id)
    {
        $this->conditions[] = new Condition\Manufacturer($id);
    }

    public function price($min, $max, $customerGroupKey)
    {
        $this->conditions[] = new Condition\Price($min, $max, $customerGroupKey);
    }

    public function properties(array $values)
    {
        $this->conditions[] = new Condition\Property($values);
    }

    public function customerGroup($key)
    {
        $this->conditions[] = new Condition\CustomerGroup($key);
    }

    public function manufacturerFacet()
    {
        $this->facets[] = new Facet\Manufacturer();
    }

    public function categoryFacet()
    {
        $this->facets[] = new Facet\Category();
    }

    public function priceFacet($customerGroupKey)
    {
        $this->facets[] = new Facet\Price($customerGroupKey);
    }

    public function propertyFacet()
    {
        $this->facets[] = new Facet\Property();
    }



    public function sortByReleaseDate($direction = 'ASC')
    {
        $this->sortings[] = new Sorting\ReleaseDate($direction);
    }

    public function sortByPopularity($direction = 'ASC')
    {
        $this->sortings[] = new Sorting\Popularity($direction);
    }

    public function sortByPrice($customerGroupKey, $direction = 'ASC')
    {
        $this->sortings[] = new Sorting\Price($direction, $customerGroupKey);
    }

    public function sortByDescription($direction = 'ASC')
    {
        $this->sortings[] = new Sorting\Description($direction);
    }

    /**
     * @param Facet $facet
     */
    public function addFacet(Facet $facet)
    {
        $this->facets[] = $facet;
    }

    /**
     * @param Condition $condition
     */
    public function addCondition(Condition $condition)
    {
        $this->conditions[] = $condition;
    }

    /**
     * @param Sorting $sorting
     */
    public function addSorting(Sorting $sorting)
    {
        $this->sortings[] = $sorting;
    }

    /**
     * @param $name
     * @return null|Condition
     */
    public function getCondition($name)
    {
        foreach($this->conditions as $condition) {
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
        foreach($this->facets as $facet) {
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
        foreach($this->sortings as $sorting) {
            if ($sorting->getName() == $name) {
                return $sorting;
            }
        }
        return null;
    }
}