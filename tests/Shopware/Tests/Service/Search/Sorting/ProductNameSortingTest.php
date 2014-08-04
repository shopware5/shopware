<?php

namespace Shopware\Tests\Service\Search\Sorting;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Tests\Service\TestCase;

class ProductNameSortingTest extends TestCase
{
    protected function getProduct(
        $number,
        Context $context,
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
            array($sorting),
            array(
                'first'  => 'Charlie',
                'second' => 'Alpha',
                'third'  => 'Bravo'
            ),
            array('second', 'third', 'first')
        );
    }

    private function search(
        $sortings,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach ($products as $number => $name) {
            $data = $this->getProduct($number, $context, $category, $name);
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
