<?php

namespace Shopware\Tests\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\ShippingFreeCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Bundle\StoreFrontBundle\TestCase;

class ShippingFreeConditionTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param ShopContext $context
     * @param bool $shippingFree
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $shippingFree = true
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['mainDetail']['shippingFree'] = $shippingFree;

        return $product;
    }

    public function testShippingFree()
    {
        $condition = new ShippingFreeCondition();
        $this->search(
            array(
                'first'  => true,
                'second' => false,
                'third'  => true
            ),
            array('first', 'third'),
            null,
            array($condition)
        );
    }
}
