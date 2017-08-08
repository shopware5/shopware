<?php

namespace Shopware\Tax\Gateway;

use Shopware\Cart\Delivery\ShippingLocation;
use Shopware\CustomerGroup\Struct\CustomerGroup;
use Shopware\Tax\Struct\TaxCollection;

class TaxRepository
{
    /**
     * @var TaxReader
     */
    private $reader;

    public function __construct(TaxReader $reader)
    {
        $this->reader = $reader;
    }

    public function getRules(CustomerGroup $customerGroup, ShippingLocation $location): TaxCollection
    {
        return $this->reader->getRules($customerGroup, $location);
    }

}