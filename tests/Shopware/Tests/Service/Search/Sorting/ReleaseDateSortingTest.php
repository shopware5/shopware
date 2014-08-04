<?php

namespace Shopware\Tests\Service\Search\Sorting;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Tests\Service\TestCase;

class ReleaseDateSortingTest extends TestCase
{
    protected function getProduct(
        $number,
        Context $context,
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
            array($sorting),
            array(
                'first'  => '2014-01-01',
                'second' => '2013-04-03',
                'third'  => '2014-12-12',
                'fourth' => '2012-01-03'
            ),
            array('fourth', 'second', 'first', 'third')
        );
    }

    private function search(
        $sortings,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach ($products as $number => $releaseDate) {
            $data = $this->getProduct($number, $context, $category, $releaseDate);
            $this->helper->createArticle($data);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));

        foreach ($sortings as $sorting) {
            $criteria->addSorting($sorting);
        }

        $result = Shopware()->Container()->get('product_number_search_dbal')
            ->search($criteria, $context);

        $this->assertSearchResult($result, $expectedNumbers);

        $this->assertSearchResultSorting($result, $expectedNumbers);
    }

}
