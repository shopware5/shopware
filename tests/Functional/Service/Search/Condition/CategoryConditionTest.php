<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class CategoryConditionTest extends TestCase
{
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $additionally = null
    ) {
        return parent::getProduct($number, $context, $additionally);
    }


    /**
     * Override prevents a default category condition
     * @param Criteria $criteria
     * @param Category $category
     * @param $conditions
     * @param ShopContext $context
     */
    protected function addCategoryBaseCondition(
        Criteria $criteria,
        Category $category,
        $conditions,
        ShopContext $context
    ) {
    }

    public function testMultipleCategories()
    {
        $first   = $this->helper->createCategory(array('name' => 'first-category'));
        $second  = $this->helper->createCategory(array('name' => 'second-category'));

        $condition = new CategoryCondition(array(
            $first->getId(),
            $second->getId()
        ));

        $this->search(
            array(
                'first'  => $first,
                'second' => $second,
                'third'  => null,
                'fourth' => $first
            ),
            array('first', 'second', 'fourth'),
            null,
            array($condition)
        );
    }
}
