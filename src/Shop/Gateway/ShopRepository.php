<?php

namespace Shopware\Shop\Gateway;

use Shopware\Context\Struct\TranslationContext;
use Shopware\Shop\Struct\ShopCollection;

class ShopRepository
{
    /**
     * @var ShopReader
     */
    private $reader;

    public function __construct(ShopReader $reader)
    {
        $this->reader = $reader;
    }

    public function read(array $ids, TranslationContext $context): ShopCollection
    {
        return $this->reader->read($ids, $context);
    }
}