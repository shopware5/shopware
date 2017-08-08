<?php

namespace Shopware\ShippingMethod\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\ShippingMethod\Struct\ShippingMethodCollection;

class ShippingMethodRepository
{
    /**
     * @var ShippingMethodReader
     */
    private $reader;

    public function __construct(ShippingMethodReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(array $ids, TranslationContext $context): ShippingMethodCollection
    {
        return $this->reader->read($ids, $context);
    }

}