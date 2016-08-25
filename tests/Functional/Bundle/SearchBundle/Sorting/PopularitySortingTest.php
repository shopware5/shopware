<?php

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Sorting;

use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class PopularitySortingTest extends TestCase
{
    protected function createProduct(
        $number,
        ShopContext $context,
        Category $category,
        $sales
    ) {
        $article = parent::createProduct(
            $number,
            $context,
            $category,
            $sales
        );

        Shopware()->Db()->query(
            "UPDATE s_articles_top_seller_ro SET sales = ?
             WHERE article_id = ?",
            array($sales, $article->getId())
        );

        return $article;
    }


    public function testAscendingSorting()
    {
        $sorting = new PopularitySorting();

        $this->search(
            array(
                'first'  => 3,
                'second' => 20,
                'third'  => 1
            ),
            array('third', 'first', 'second'),
            null,
            array(),
            array(),
            array($sorting)
        );
    }

    public function testDescendingSorting()
    {
        $sorting = new PopularitySorting(SortingInterface::SORT_DESC);

        $this->search(
            array(
                'first'  => 3,
                'second' => 20,
                'third'  => 1
            ),
            array('second', 'first', 'third'),
            null,
            array(),
            array(),
            array($sorting)
        );
    }

    public function testSalesEquals()
    {
        $sorting = new PopularitySorting(SortingInterface::SORT_DESC);

        $this->search(
            array(
                'first'  => 3,
                'second' => 20,
                'third'  => 1,
                'fourth'  => 20
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
