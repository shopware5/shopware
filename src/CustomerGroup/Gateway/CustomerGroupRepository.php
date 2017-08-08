<?php

namespace Shopware\CustomerGroup\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\CustomerGroup\Struct\CustomerGroupCollection;

class CustomerGroupRepository
{
    /**
     * @var CustomerGroupReader
     */
    private $reader;

    public function __construct(CustomerGroupReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(array $ids, TranslationContext $context): CustomerGroupCollection
    {
        return $this->reader->read($ids, $context);
    }

}