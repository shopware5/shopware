<?php

namespace Shopware\Currency\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Currency\Struct\CurrencyCollection;

class CurrencyRepository
{
    /**
     * @var CurrencyReader
     */
    private $reader;

    public function __construct(CurrencyReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(array $ids, TranslationContext $context): CurrencyCollection
    {
        return $this->reader->read($ids, $context);
    }

}