<?php

namespace Shopware\Tests\Service\Search;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Helper;

class CategoryConditionTest extends TestCase
{
    /**
     * @var Helper
     */
    private $helper;

    protected function setUp()
    {
        $this->helper = new Helper();
        parent::setUp();
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
     * @param Category[] $categories
     * @param Context $context
     * @return array
     */
    private function getDefaultProduct($number, array $categories, Context $context)
    {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $product['categories'] = array();
        foreach($categories as $category) {
            $product['categories'][] = array('id' => $category->getId());
        }

        return $product;
    }

    public function testSingleCategory()
    {
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getDefaultProduct('testSingleCategory-1', array($category), $context),
            $this->getDefaultProduct('testSingleCategory-2', array($category), $context),
            $this->getDefaultProduct('testSingleCategory-3', array($category), $context),
            $this->getDefaultProduct('testSingleCategory-4', array(), $context),
        );

        foreach($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->category(array($category->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testSingleCategory-1', 'testSingleCategory-2', 'testSingleCategory-3')
        );
    }

    public function testMultipleCategories()
    {
        $category = $this->helper->createCategory();
        $second = $this->helper->createCategory(array('name' => 'Multiple-Categories-Test'));
        $context = $this->getContext();

        $articles = array(
            $this->getDefaultProduct('testMultipleCategories-1', array($category), $context),
            $this->getDefaultProduct('testMultipleCategories-2', array($category), $context),
            $this->getDefaultProduct('testMultipleCategories-3', array($second), $context),
            $this->getDefaultProduct('testMultipleCategories-4', array($second), $context),
        );

        foreach($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->category(array($category->getId(), $second->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testMultipleCategories-1', 'testMultipleCategories-2', 'testMultipleCategories-3', 'testMultipleCategories-4')
        );
    }
}