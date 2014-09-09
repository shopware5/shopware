<?php

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;

/**
 * Class LocationContext
 * @package Shopware\Bundle\StoreFrontBundle\Struct
 */
class LocationContext
    extends Extendable
    implements LocationContextInterface
{
    /**
     * @var Area
     */
    protected $area;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param Area $area
     * @param Country $country
     * @param State $state
     */
    public function __construct(Area $area, Country $country, State $state)
    {
        $this->area = $area;
        $this->country = $country;
        $this->state = $state;
    }

    /**
     * @return Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }
}