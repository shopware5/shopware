<?php

namespace Shopware\Gateway\Search\Condition;

use Shopware\Gateway\Search\Condition;

class Manufacturer extends Condition
{
    /**
     * @var array
     */
    private $manufacturerIds;

    function __construct(array $manufacturerIds)
    {
        $this->manufacturerIds = $manufacturerIds;
    }

    public function getName()
    {
        return 'manufacturer';
    }

    /**
     * @return array
     */
    public function getManufacturerIds()
    {
        return $this->manufacturerIds;
    }
}