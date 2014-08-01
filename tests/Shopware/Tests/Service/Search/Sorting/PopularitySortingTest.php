<?php

namespace Shopware\Tests\Service\Search\Sorting;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Tests\Service\Search\TestCase;

class PopularitySortingTest extends TestCase
{
    public function testAscendingSorting()
    {
        $sorting = new PopularitySorting();

        $this->search(
            $sorting,
            array(
                'first'  => 3,
                'second' => 20,
                'third'  => 1
            ),
            array('third', 'first', 'second')
        );
    }

    public function testDescendingSorting()
    {
        $sorting = new PopularitySorting(SortingInterface::SORT_DESC);

        $this->search(
            $sorting,
            array(
                'first'  => 3,
                'second' => 20,
                'third'  => 1
            ),
            array('second', 'first', 'third')
        );
    }

    public function testSalesEquals()
    {
        $sorting = new PopularitySorting(SortingInterface::SORT_DESC);

        $this->search(
            $sorting,
            array(
                'first'  => 3,
                'second' => 20,
                'third'  => 1,
                'fourth'  => 20
            ),
            array('fourth', 'second', 'first', 'third')
        );
    }

    private function search(
        PopularitySorting $sorting,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach($products as $number => $sales) {
            $data = $this->getProduct($number, $context, $category);
            $article = $this->helper->createArticle($data);

            Shopware()->Db()->query(
                "UPDATE s_articles_top_seller_ro SET sales = ?
                 WHERE article_id = ?",
                array($sales, $article->getId())
            );
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addSorting($sorting);

        $result = Shopware()->Container()->get('product_number_search_dbal')
            ->search($criteria, $context);

        $this->assertSearchResult($result, $expectedNumbers);

        $this->assertSearchResultSorting($result, $expectedNumbers);
    }

}
