<?php

namespace Shopware\Country\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Country\Struct\CountryCollection;

class CountryRepository
{
    /**
     * @var CountryReader
     */
    private $reader;

    public function __construct(CountryReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(array $ids, TranslationContext $context): CountryCollection
    {
        return $this->reader->read($ids, $context);
    }
}