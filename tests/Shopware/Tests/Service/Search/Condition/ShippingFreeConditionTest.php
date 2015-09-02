<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\ShippingFreeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class ShippingFreeConditionTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Context $context
     * @param bool $shippingFree
     * @return array
     */
    protected function getProduct(
        $number,
        Context $context,
        Category $category = null,
        $shippingFree = true
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['mainDetail']['shippingFree'] = $shippingFree;

        return $product;
    }

    public function testShippingFree()
    {
        $context = $this->getContext();

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
