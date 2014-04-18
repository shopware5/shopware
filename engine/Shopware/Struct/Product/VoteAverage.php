<?php

namespace Shopware\Struct\Product;

/**
 * @package Shopware\Struct
 */
class VoteAverage
{
    /**
     * @var int
     */
    private $count;

    /**
     * @var float
     */
    private $average;

    /**
     * @var array
     */
    private $pointCount;

    /**
     * @param float $average
     */
    public function setAverage($average)
    {
        $this->average = $average;
    }

    /**
     * @return float
     */
    public function getAverage()
    {
        return $this->average;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return array
     */
    public function getPointCount()
    {
        return $this->pointCount;
    }

    /**
     * @param array $pointCount
     */
    public function setPointCount($pointCount)
    {
        $this->pointCount = $pointCount;
    }
}