<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class PriceConditionTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param ShopContext $context
     * @param $prices
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $prices = array()
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['mainDetail']['prices'] = [];
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
        $context = $this->getContext();
        $context->setFallbackCustomerGroup($this->getEkCustomerGroup());

        $condition = new PriceCondition(10, 20);

        $this->search(
            [
                'first'  => ['EK' => 21],
                'second' => ['EK' => 10],
                'third'  => ['EK' => 15],
                'fourth' => ['EK' => 20],
            ],
            ['second', 'third', 'fourth'],
            null,
            [$condition],
            [],
            [],
            $context
        );
    }

    public function testDecimalPriceRange()
    {
        $context = $this->getContext();
        $context->setFallbackCustomerGroup($this->getEkCustomerGroup());
        $condition = new PriceCondition(10, 20);

        $this->search(
            array(
                'first'  => array('EK' => 9.99),
                'second' => array('EK' => 10.01),
                'third'  => array('EK' => 19.98)
            ),
            array('second', 'third'),
            null,
            array($condition),
            [],
            [],
            $context
        );
    }

    public function testCustomerGroupPrices()
    {
        $context = $this->getContext();

        $customerGroup = $this->helper->createCustomerGroup(['key' => 'CUST']);
        $context->setCurrentCustomerGroup(
            $this->converter->convertCustomerGroup($customerGroup)
        );

        $context->setFallbackCustomerGroup($this->getEkCustomerGroup());

        $condition = new PriceCondition(10, 20);

        $this->search(
            array(
                'first'  => array('EK' => 21),
                'second' => array('EK' => 15),
                'third'  => array('EK' => 15, 'CUST' => 5),
                'fourth' => array('EK' => 3,  'CUST' => 15),
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
        $context->getCurrency()->setFactor(1.3625);
        $context->getCurrency()->setId(2);

        $condition = new PriceCondition(12, 29);
        $context->setFallbackCustomerGroup($this->getEkCustomerGroup());

        $this->search(
            array(
                'first'  => array('EK' => 10),
                'second' => array('EK' => 20),
                'third'  => array('EK' => 30)
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
