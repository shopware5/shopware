<?php

namespace Shopware\Address\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Address\Struct\AddressCollection;

class AddressRepository
{
    /**
     * @var AddressReader
     */
    private $reader;

    public function __construct(AddressReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(array $ids, TranslationContext $context): AddressCollection
    {
        return $this->reader->read($ids, $context);
    }
}