<?php

namespace Shopware\Currency\Struct;

use Shopware\Framework\Struct\Collection;
use Shopware\Search\SearchResultInterface;
use Shopware\Search\SearchResultTrait;
use Shopware\Shop\Struct\Shop;
use Shopware\Shop\Struct\ShopIdentity;

class ShopSearchResult extends Collection implements SearchResultInterface
{
    use SearchResultTrait;

    /**
     * @var ShopIdentity[]
     */
    protected $elements = [];

    public function __construct(array $elements, int $total)
    {
        parent::__construct($elements);
        $this->total = $total;
    }

    public function sortByPosition(): ShopSearchResult
    {
        $this->sort(function(ShopIdentity $a, ShopIdentity $b) {
            return $a->getPosition() <=> $b->getPosition();
        });
        return $this;
    }

    public function add(ShopIdentity $shop): void
    {
        $this->elements[$this->getKey($shop)] = $shop;
    }

    private function getKey(ShopIdentity $shop): int
    {
        return $shop->getId();
    }

    public function getIds(): array
    {
        return $this->getKeys();
    }
}

