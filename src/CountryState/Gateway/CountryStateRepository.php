<?php

namespace Shopware\CountryState\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\CountryState\Struct\CountryStateCollection;

class CountryStateRepository
{
    /**
     * @var CountryStateReader
     */
    private $reader;

    public function __construct(CountryStateReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(array $ids, TranslationContext $context): CountryStateCollection
    {
        return $this->reader->read($ids, $context);
    }
}