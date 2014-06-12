<?php

namespace Shopware\Gateway\Search;

class Result
{
    /**
     * @var Product[]
     */
    protected $products;

    /**
     * @var int
     */
    protected $totalCount;

    /**
     * @var array
     */
    protected $facets;

    function __construct(array $products, $totalCount, $facets)
    {
        $this->products = $products;
        $this->totalCount = $totalCount;
        $this->facets = $facets;
    }

    /**
     * @return \Shopware\Gateway\Search\Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return array
     */
    public function getFacets()
    {
        return $this->facets;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }


}
