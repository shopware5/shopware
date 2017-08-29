<?php

namespace StorefrontBundle\Service;

use Shopware\Search\Criteria;
use Shopware\Context\Struct\ShopContext;
use StorefrontBundle\Struct\ProductSearchResult;

class ProductSearch
{
    public function search(Criteria $criteria, ShopContext $context): ProductSearchResult
    {
        // prepare criteria and add "frontend conditions" as base conditions

        // fetch list products

        // return new result
    }
}