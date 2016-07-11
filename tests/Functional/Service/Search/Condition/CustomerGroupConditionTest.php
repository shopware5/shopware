<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\CustomerGroupCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group;
use Shopware\Tests\Service\TestCase;

class CustomerGroupConditionTest extends TestCase
{
    /**
     * @param $number
     * @param Group[] $customerGroups
     * @param \Shopware\Models\Category\Category $category
     * @param ShopContext $context
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $customerGroups = null
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['customerGroups'] = array();
        foreach ($customerGroups as $customerGroup) {
            $product['customerGroups'][] = array('id' => $customerGroup->getId());
        }

        return $product;
    }

    public function testSingleCustomerGroup()
    {
        $customerGroup = $this->helper->createCustomerGroup(array('key' => 'CON'));

        $this->search(
            array(
                'first' => array($customerGroup),
                'second' => array($customerGroup),
                'third' => null,
                'fourth' => null
            ),
            array('third', 'fourth'),
            null,
            array(new CustomerGroupCondition(array($customerGroup->getId())))
        );
    }

    public function testMultipleCustomerGroups()
    {
        $first = $this->helper->createCustomerGroup(array('key' => 'CON'));
        $second = $this->helper->createCustomerGroup(array('key' => 'CON2'));

        $condition = new CustomerGroupCondition(array($first->getId(), $second->getId()));

        $this->search(
            array(
                'first' => array($first),
                'second' => array($second),
                'third' => array($first, $second),
                'fourth' => null
            ),
            array('fourth'),
            null,
            array($condition)
        );
    }
}
