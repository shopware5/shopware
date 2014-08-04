<?php

namespace Shopware\Tests\Service\Search\Sorting;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\ProductAttributeSorting;
use Shopware\Tests\Service\TestCase;

class ProductAttributeSortingTest extends TestCase
{
    protected function getProduct(
        $number,
        Context $context,
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
            array($sorting),
            array(
                'first'  => array('attr1' => 'Charlie'),
                'second' => array('attr1' => 'Alpha'),
                'third'  => array('attr1' => 'Bravo'),
            ),
            array('second', 'third', 'first')
        );
    }

    public function testMultipleFieldSorting()
    {
        $this->search(
            array(
                new ProductAttributeSorting('attr1'),
                new ProductAttributeSorting('attr2')
            ),
            array(
                'first'  => array('attr1' => 'Charlie'),
                'second' => array('attr1' => 'Alpha'),
                'third'  => array('attr1' => 'Bravo', 'attr2' => 'Bravo'),
                'fourth'  => array('attr1' => 'Bravo', 'attr2' => 'Alpha'),
            ),
            array('second', 'fourth', 'third', 'first')
        );
    }

    private function search(
        $sortings,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach ($products as $number => $attribute) {
            $data = $this->getProduct($number, $context, $category, $attribute);
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
