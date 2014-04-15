<?php

namespace Shopware\Gateway\Search;

class Result
{
    /**
     * @var array
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

    function __construct($products, $totalCount, $facets)
    {
        $this->products = $products;
        $this->totalCount = $totalCount;
        $this->facets = $facets;
    }
}