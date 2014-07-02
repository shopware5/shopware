<?php

namespace Shopware\Tests\Service\Search;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;

class PriceConditionTest extends TestCase
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Converter
     */
    private $converter;

    protected function setUp()
    {
        $this->helper = new Helper();
        $this->converter = new Converter();
        parent::setUp();
    }

    protected function tearDown()
    {
        $this->helper->cleanUp();
        parent::tearDown();
    }

    /**
     * @return Context
     */
    private function getContext()
    {
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();

        $shop = $this->helper->getShop();

        return $this->helper->createContext(
            $customerGroup,
            $shop,
            array($tax)
        );
    }

    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Context $context
     * @param $price
     * @return array
     */
    private function getDefaultProduct(
        $number,
        Category $category,
        Context $context,
        $price
    ) {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $product['categories'] = array(
            array('id' => $category->getId())
        );

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
            $this->getDefaultProduct('testSimplePriceRange-1', $category, $context, 21),
            $this->getDefaultProduct('testSimplePriceRange-2', $category, $context, 10),
            $this->getDefaultProduct('testSimplePriceRange-3', $category, $context, 15),
            $this->getDefaultProduct('testSimplePriceRange-4', $category, $context, 20),
        );

        foreach($articles as $article) {
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
            $this->getDefaultProduct('testDecimalPriceRange-2', $category, $context, 9.99),
            $this->getDefaultProduct('testDecimalPriceRange-3', $category, $context, 10.01),
            $this->getDefaultProduct('testDecimalPriceRange-4', $category, $context, 19.98),
        );

        foreach($articles as $article) {
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
            $this->getDefaultProduct('testCustomerGroupPrices-1', $category, $context, 21),
            $this->getDefaultProduct('testCustomerGroupPrices-2', $category, $context, 15),
        );

        /**
         * Fallback customer group price: 15
         * Current customer group price : 3
         *
         * Shouldn't match price condition
         */
        $product = $this->getDefaultProduct('testCustomerGroupPrices-3', $category, $context, 15);
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
        $product = $this->getDefaultProduct('testCustomerGroupPrices-4', $category, $context, 3);
        $product['mainDetail']['prices'][] = array(
            'from' => 1,
            'to' => 'beliebig',
            'price' => 15,
            'customerGroupKey' => $context->getCurrentCustomerGroup()->getKey()
        );
        $articles[] = $product;

        foreach($articles as $article) {
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

}