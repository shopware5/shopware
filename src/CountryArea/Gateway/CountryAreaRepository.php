<?php

namespace Shopware\CountryArea\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\CountryArea\Struct\CountryAreaCollection;

class CountryAreaRepository
{
    /**
     * @var CountryAreaReader
     */
    private $reader;

    public function __construct(CountryAreaReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(array $ids, TranslationContext $context): CountryAreaCollection
    {
        return $this->reader->read($ids, $context);
    }

}