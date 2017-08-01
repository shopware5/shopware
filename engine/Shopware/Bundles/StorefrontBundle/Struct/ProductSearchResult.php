<?php

namespace StorefrontBundle\Struct;

use ProductBundle\Struct\ProductCollection;

class ProductSearchResult
{
    /**
     * @var ProductCollection
     */
    protected $products;

    /**
     * @var int
     */
    protected $total;

    public function __construct(ProductCollection $products, $total)
    {
        $this->products = $products;
        $this->total = $total;
    }

    public function getProducts(): ProductCollection
    {
        return $this->products;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}