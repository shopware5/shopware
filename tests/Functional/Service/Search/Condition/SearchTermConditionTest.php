<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class SearchTermConditionTest extends TestCase
{
    /**
     * @param $number
     * @param ShopContext $context
     * @param Category $category
     * @param $name
     * @return array
     */
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

    public function testSingleMatch()
    {
        $condition = new SearchTermCondition('Unit');

        $this->search(
            array(
                'first'  => 'Default Product',
                'second' => 'UnitTest Product',
                'third'  => 'Custom Product'
            ),
            array('second'),
            null,
            array($condition)
        );
    }

    public function testMultipleMatch()
    {
        $condition = new SearchTermCondition('unit');

        $this->search(
            array(
                'first'  => 'Default Unit Product',
                'second' => 'Unit Test Product',
                'third'  => 'Custom Product Unit',
                'fourth' => 'Custom produniuct'
            ),
            array('first', 'second', 'third'),
            null,
            array($condition)
        );
    }

    public function createProducts($products, ShopContext $context, Category $category)
    {
        $articles = parent::createProducts($products, $context, $category);

        Shopware()->Container()->get('shopware_searchdbal.search_indexer')->build();

        Shopware()->Container()->get('cache')->clean('all', array('Shopware_Modules_Search'));

        return $articles;
    }
}
