<?php

namespace Shopware\Gateway\Search;

use Shopware\Gateway\Search\Condition as Condition;
use Shopware\Gateway\Search\Facet as Facet;

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

    public function price($min, $max)
    {
        $this->conditions[] = new Condition\Price($min, $max);
    }

    public function addCondition(Condition $condition)
    {
        $this->conditions[] = $condition;
    }

    public function manufacturerFacet()
    {
        $this->facets[] = new Facet\Manufacturer();
    }

    public function categoryFacet()
    {
        $this->facets[] = new Facet\Category();
    }

    public function addFacet(Facet $facet)
    {
        $this->facets[] = $facet;
    }
}