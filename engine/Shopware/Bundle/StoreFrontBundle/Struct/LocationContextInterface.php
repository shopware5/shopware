<?php

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;

/**
 * Interface LocationContextInterface
 * @package Shopware\Bundle\StoreFrontBundle\Struct
 */
interface LocationContextInterface
{
    /**
     * @return Area
     */
    public function getArea();

    /**
     * @return Country
     */
    public function getCountry();

    /**
     * @return State
     */
    public function getState();
}