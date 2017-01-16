<?php

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\IsNewCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class IsNewConditionTest extends TestCase
{
    public function testIsNew()
    {
        $condition = new IsNewCondition();

        $this->search(
            array(
                'first'  => array('added' => date("Y-m-d", strtotime('-60 days'))),
                'second' => array('added' => date("Y-m-d", strtotime('-31 days'))),
                'third'  => array('added' => '2011-01-01'),
                'fourth' => array('added' => date("Y-m-d", strtotime('-20 days'))),
                'fifth' => array('added' => date("Y-m-d")),
                'sixth' => array('added' => date("Y-m-d", strtotime('-30 days')))
            ),
            array('fourth', 'fifth', 'sixth'),
            null,
            array($condition)
        );
    }

    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param ShopContext $context
     * @param array $data
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $data = []
    ) {
        $product = parent::getProduct($number, $context, $category);
        $product = array_merge($product, $data);

        return $product;
    }
}
