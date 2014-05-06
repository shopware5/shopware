<?php

namespace Shopware\Gateway\Search;

abstract class Sorting
{
    /**
     * @var string
     */
    protected $direction;

    /**
     * @param string $direction
     */
    function __construct($direction = 'ASC')
    {
        $this->direction = $direction;
    }

    /**
     * @param string $direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    abstract function getName();
}