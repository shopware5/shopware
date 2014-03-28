<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;

/**
 * @package Shopware\Service
 */
class GlobalState
{
    /**
     * @return Struct\GlobalState
     */
    public function get()
    {

        return new Struct\GlobalState();
    }
}