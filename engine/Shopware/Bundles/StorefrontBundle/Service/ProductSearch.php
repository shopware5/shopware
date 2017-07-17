<?php

namespace StorefrontBundle\Service;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use StorefrontBundle\Struct\ProductSearchResult;

class ProductSearch
{
    public function search(Criteria $criteria, ShopContextInterface $context): ProductSearchResult
    {
        // prepare criteria and add "frontend conditions" as base conditions

        // fetch list products

        // return new result
    }
}