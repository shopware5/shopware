<?php

namespace StorefrontBundle\Struct;

use CategoryBundle\Struct\Category;
use ProductBundle\Struct\ProductCollection;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\StoreFrontBundle\Common\Struct;

class ListingPage extends Struct
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @var int
     */
    protected $total;

    /**
     * @var ProductCollection
     */
    protected $products;

    /**
     * @var FacetResultInterface[]
     */
    protected $aggregations;

    public function __construct(Category $category, int $total, ProductCollection $products, array $aggregations)
    {
        $this->category = $category;
        $this->total = $total;
        $this->products = $products;
        $this->aggregations = $aggregations;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getProducts(): ProductCollection
    {
        return $this->products;
    }

    public function getAggregations(): array
    {
        return $this->aggregations;
    }
}