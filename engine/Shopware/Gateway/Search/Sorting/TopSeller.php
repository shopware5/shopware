<?php

namespace Shopware\Gateway\Search\Sorting;

use Shopware\Gateway\Search\Sorting;

class TopSeller extends Sorting
{
    /**
     * @param string $direction
     */
    function __construct($direction = 'DESC')
    {
        $this->direction = $direction;
    }
}