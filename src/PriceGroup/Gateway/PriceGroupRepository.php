<?php

namespace Shopware\PriceGroup\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\CustomerGroup\Struct\CustomerGroup;
use Shopware\PriceGroup\Struct\PriceGroupCollection;

class PriceGroupRepository
{
    /**
     * @var PriceGroupReader
     */
    private $reader;

    public function __construct(PriceGroupReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(CustomerGroup $customerGroup, TranslationContext $context): PriceGroupCollection
    {
        return $this->reader->read($customerGroup, $context);
    }
}