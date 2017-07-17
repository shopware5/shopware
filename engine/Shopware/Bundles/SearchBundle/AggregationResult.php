<?php

namespace SearchBundle;

use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\StoreFrontBundle\Common\Struct;

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