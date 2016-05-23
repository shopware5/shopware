<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class PriceConditionTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param ProductContext $context
     * @param $prices
     * @return array
     */
    protected function getProduct(
        $number,
        ProductContext $context,
        Category $category = null,
        $prices = array()
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['mainDetail']['prices'] = array();
        foreach ($prices as $key => $price) {
            if ($key === $context->getCurrentCustomerGroup()->getKey()) {
                $customerGroup = $context->getCurrentCustomerGroup()->getKey();
            } else {
                $customerGroup = $context->getFallbackCustomerGroup()->getKey();
            }

            $product['mainDetail']['prices'][] = array(
                 'from' => 1,
                 'to' => 'beliebig',
                 'price' => $price,
                 'customerGroupKey' => $customerGroup
             );
        }

        return $product;
    }

    public function testSimplePriceRange()
    {
        $condition = new PriceCondition(10, 20);

        $this->search(
            array(
                'first'  => array('PHP' => 21),
                'second' => array('PHP' => 10),
                'third'  => array('PHP' => 15),
                'fourth' => array('PHP' => 20),
            ),
            array('second', 'third', 'fourth'),
            null,
            array($condition)
        );
    }

    public function testDecimalPriceRange()
    {
        $condition = new PriceCondition(10, 20);

        $this->search(
            array(
                'first'  => array('PHP' => 9.99),
                'second' => array('PHP' => 10.01),
                'third'  => array('PHP' => 19.98)
            ),
            array('second', 'third'),
            null,
            array($condition)
        );
    }


    public function testCustomerGroupPrices()
    {
        $context = $this->getContext();

        $customerGroup = $this->helper->createCustomerGroup(array('key' => 'CUST'));
        $context->setCurrentCustomerGroup(
            $this->converter->convertCustomerGroup($customerGroup)
        );

        $condition = new PriceCondition(10, 20);

        $this->search(
            array(
                'first'  => array('PHP' => 21),
                'second' => array('PHP' => 15),
                'third'  => array('PHP' => 15, 'CUST' => 5),
                'fourth' => array('PHP' => 3,  'CUST' => 15),
            ),
            array('second', 'fourth'),
            null,
            array($condition),
            array(),
            array(),
            $context
        );
    }

    public function testPriceConditionWithCurrencyFactor()
    {
        $context = $this->getContext();
        $context->getCurrency()->setFactor(2.5);
        $condition = new PriceCondition(25, 50);

        $this->search(
            array(
                'first'  => array('PHP' => 10),
                'second' => array('PHP' => 20),
                'third'  => array('PHP' => 30)
            ),
            array('first', 'second'),
            null,
            array($condition),
            array(),
            array(),
            $context
        );
    }
}
