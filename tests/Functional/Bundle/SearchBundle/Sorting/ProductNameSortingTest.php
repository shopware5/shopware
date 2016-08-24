<?php

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Sorting;

use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class ProductNameSortingTest extends TestCase
{
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $name = null
    ) {
        $product = parent::getProduct($number, $context, $category);
        $product['name'] = $name;

        return $product;
    }


    public function testNameSorting()
    {
        $sorting = new ProductNameSorting();

        $this->search(
            array(
                'first'  => 'Charlie',
                'second' => 'Alpha',
                'third'  => 'Bravo'
            ),
            array('second', 'third', 'first'),
            null,
            array(),
            array(),
            array($sorting)
        );
    }

    protected function search(
        $products,
        $expectedNumbers,
        $category = null,
        $conditions = array(),
        $facets = array(),
        $sortings = array(),
        $context = null
    ) {
        $result = parent::search(
            $products,
            $expectedNumbers,
            $category,
            $conditions,
            $facets,
            $sortings,
            $context
        );

        $this->assertSearchResultSorting($result, $expectedNumbers);

        return $result;
    }
}
