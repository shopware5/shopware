<?php

namespace Shopware\Tests\Service\Search\Sorting;

use Shopware\Bundle\SearchBundle\Sorting\ProductAttributeSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class ProductAttributeSortingTest extends TestCase
{
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $attribute = array('attr1' => null)
    ) {
        $product = parent::getProduct($number, $context, $category);
        $product['mainDetail']['attribute'] = $attribute;

        return $product;
    }


    public function testSingleFieldSorting()
    {
        $sorting = new ProductAttributeSorting('attr1');

        $this->search(
            array(
                'first'  => array('attr1' => 'Charlie'),
                'second' => array('attr1' => 'Alpha'),
                'third'  => array('attr1' => 'Bravo'),
            ),
            array('second', 'third', 'first'),
            null,
            array(),
            array(),
            array($sorting)
        );
    }

    public function testMultipleFieldSorting()
    {
        $this->search(
            array(
                'first'  => array('attr1' => 'Charlie'),
                'second' => array('attr1' => 'Alpha'),
                'third'  => array('attr1' => 'Bravo', 'attr2' => 'Bravo'),
                'fourth'  => array('attr1' => 'Bravo', 'attr2' => 'Alpha'),
            ),
            array('second', 'fourth', 'third', 'first'),
            null,
            array(),
            array(),
            array(
                new ProductAttributeSorting('attr1'),
                new ProductAttributeSorting('attr2')
            )
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
