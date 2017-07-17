<?php

namespace StorefrontBundle\Service;

use ProductBundle\Gateway\Aggregator\ListingPriceAggregator;
use ProductBundle\Struct\ListingPriceCollection;
use Shopware\Bundle\StorefrontBundle\Context\ShopContextInterface;

class ListingPriceService
{
    /**
     * @var ListingPriceAggregator
     */
    private $aggregator;

    public function __construct(ListingPriceAggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    public function read($numbers, ShopContextInterface $context): ListingPriceCollection
    {
        //fetch prices for current customer group

        //compare if all numbers has prices

        //fetch fallback prices
    }
}