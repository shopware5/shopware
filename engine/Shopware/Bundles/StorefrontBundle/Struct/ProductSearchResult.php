<?php

namespace StorefrontBundle\Struct;

use Shopware\Product\Struct\ProductCollection;

class ProductSearchResult
{
    /**
     * @var \Shopware\Product\Struct\ProductCollection
     */
    protected $products;

    /**
     * @var int
     */
    protected $total;

    public function __construct(\Shopware\Product\Struct\ProductCollection $products, $total)
    {
        $this->products = $products;
        $this->total = $total;
    }

    public function getProducts(): \Shopware\Product\Struct\ProductCollection
    {
        return $this->products;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}