<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;

class Price extends Condition
{

    /**
     * @var float
     */
    private $minPrice;

    /**
     * @var float
     */
    private $maxPrice;

    /**
     * @param $minPrice
     * @param $maxPrice
     */
    function __construct($minPrice, $maxPrice)
    {
        $this->minPrice = $minPrice;
        $this->maxPrice = $maxPrice;
    }

    public function getName()
    {
        return 'price';
    }

    /**
     * @return float
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @return float
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }
}
