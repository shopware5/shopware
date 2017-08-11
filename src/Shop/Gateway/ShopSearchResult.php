<?php

namespace Shopware\Currency\Struct;

use Shopware\Search\SearchResultInterface;
use Shopware\Search\SearchResultTrait;
use Shopware\Shop\Struct\ShopIdentity;
use Shopware\Shop\Struct\ShopIdentityCollection;

class ShopSearchResult extends ShopIdentityCollection implements SearchResultInterface
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
}

