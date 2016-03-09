<?php

namespace Shopware\Tests\Service\Search\Sorting;

use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class ReleaseDateSortingTest extends TestCase
{
    protected function getProduct(
        $number,
        ProductContext $context,
        Category $category = null,
        $releaseDate = null
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['added'] = $releaseDate;

        return $product;
    }

    public function testReleaseDateSorting()
    {
        $sorting = new ReleaseDateSorting();

        $this->search(
            array(
                'first'  => '2014-01-01',
                'second' => '2013-04-03',
                'third'  => '2014-12-12',
                'fourth' => '2012-01-03'
            ),
            array('fourth', 'second', 'first', 'third'),
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
