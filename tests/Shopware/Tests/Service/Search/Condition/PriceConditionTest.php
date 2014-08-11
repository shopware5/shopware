<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\PriceCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class PriceConditionTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Context $context
     * @param $price
     * @return array
     */
    protected function getProduct(
        $number,
        Context $context,
        Category $category = null,
        $price = 0
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['mainDetail']['prices'] = array(array(
            'from' => 1,
            'to' => 'beliebig',
            'price' => $price,
            'customerGroupKey' => $context->getFallbackCustomerGroup()->getKey()
        ));

        return $product;
    }

    public function testSimplePriceRange()
    {
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getProduct('testSimplePriceRange-1', $context, $category, 21),
            $this->getProduct('testSimplePriceRange-2', $context, $category, 10),
            $this->getProduct('testSimplePriceRange-3', $context, $category, 15),
            $this->getProduct('testSimplePriceRange-4', $context, $category, 20),
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addPriceCondition(10, 20);

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testSimplePriceRange-2', 'testSimplePriceRange-3', 'testSimplePriceRange-4')
        );
    }

    public function testDecimalPriceRange()
    {
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getProduct('testDecimalPriceRange-2', $context, $category, 9.99),
            $this->getProduct('testDecimalPriceRange-3', $context, $category, 10.01),
            $this->getProduct('testDecimalPriceRange-4', $context, $category, 19.98),
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addPriceCondition(10, 20);

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testDecimalPriceRange-3', 'testDecimalPriceRange-4')
        );
    }

    public function testCustomerGroupPrices()
    {
        $context = $this->getContext();
        $category = $this->helper->createCategory();
        $customerGroup = $this->helper->createCustomerGroup(array('key' => 'CUST'));
        $context->setCurrentCustomerGroup($this->converter->convertCustomerGroup($customerGroup));

        $articles = array(
            $this->getProduct('testCustomerGroupPrices-1', $context, $category, 21),
            $this->getProduct('testCustomerGroupPrices-2', $context, $category, 15),
        );

        /**
         * Fallback customer group price: 15
         * Current customer group price : 3
         *
         * Shouldn't match price condition
         */
        $product = $this->getProduct('testCustomerGroupPrices-3', $context, $category, 15);
        $product['mainDetail']['prices'][] = array(
            'from' => 1,
            'to' => 'beliebig',
            'price' => 5,
            'customerGroupKey' => $context->getCurrentCustomerGroup()->getKey()
        );
        $articles[] = $product;


        /**
         * Fallback customer group price: 3
         * Current customer group price : 15
         *
         * Should match price condition
         */
        $product = $this->getProduct('testCustomerGroupPrices-4', $context, $category, 3);
        $product['mainDetail']['prices'][] = array(
            'from' => 1,
            'to' => 'beliebig',
            'price' => 15,
            'customerGroupKey' => $context->getCurrentCustomerGroup()->getKey()
        );
        $articles[] = $product;

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addPriceCondition(10, 20);

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testCustomerGroupPrices-2', 'testCustomerGroupPrices-4')
        );
    }

    public function testPriceConditionWithCurrencyFactor()
    {
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $context->getCurrency()->setFactor(2.5);

        $articles = array(
            $this->getProduct('first',  $context, $category, 10),
            $this->getProduct('second', $context, $category, 20),
            $this->getProduct('third',  $context, $category, 30),
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addPriceCondition(25, 50);

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('first', 'second')
        );

    }
}
