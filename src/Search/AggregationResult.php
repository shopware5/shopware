<?php

namespace Shopware\Search;

use Shopware\Search\FacetResultInterface;
use Shopware\Framework\Struct\Struct;

class AggregationResult extends Struct
{
    /**
     * @var FacetResultInterface[]
     */
    public $aggregations;

    public function __construct(array $aggregations)
    {
        $this->aggregations = $aggregations;
    }
}