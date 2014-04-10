<?php

namespace Shopware\Gateway;
use Shopware\Struct as Struct;

interface Vote
{
    /**
     * Selects the aggregated product vote meta information.
     * This data contains the total of the product votes,
     * the average value of the rating and the count of each
     * different point rating.
     *
     * @param Struct\ProductMini $product
     * @return \Shopware\Struct\VoteAverage
     */
    public function getAverage(Struct\ProductMini $product);
}