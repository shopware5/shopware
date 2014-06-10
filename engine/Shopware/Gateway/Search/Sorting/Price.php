<?php

namespace Shopware\Gateway\Search\Sorting;

use Shopware\Gateway\Search\Sorting;

class Price extends Sorting
{
    /**
     * @param string $direction
     */
    function __construct($direction = 'ASC')
    {
        $this->direction = $direction;
    }

    function getName()
    {
        return 'prices';
    }
}