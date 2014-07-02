<?php

namespace Shopware\Tests\Service\Search;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group;
use Shopware\Tests\Service\Helper;

class CustomerGroupConditionTest extends TestCase
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
     * @param Group[] $customerGroups
     * @param \Shopware\Models\Category\Category $category
     * @param Context $context
     * @return array
     */
    private function getDefaultProduct(
        $number,
        array $customerGroups,
        Category $category,
        Context $context
    ) {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $product['categories'] = array(
            array('id' => $category->getId())
        );

        $product['customerGroups'] = array();
        foreach($customerGroups as $customerGroup) {
            $product['customerGroups'][] = array('id' => $customerGroup->getId());
        }

        return $product;
    }

    public function testSingleCustomerGroup()
    {
        $customerGroup = $this->helper->createCustomerGroup(array('key' => 'CON'));
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getDefaultProduct('testSingleCustomerGroup-1', array($customerGroup), $category, $context),
            $this->getDefaultProduct('testSingleCustomerGroup-2', array($customerGroup), $category, $context),
            $this->getDefaultProduct('testSingleCustomerGroup-3', array(), $category, $context),
            $this->getDefaultProduct('testSingleCustomerGroup-4', array(), $category, $context),
        );

        foreach($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addCustomerGroupCondition(array($customerGroup->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testSingleCustomerGroup-3', 'testSingleCustomerGroup-4')
        );
    }

    public function testMultipleCategories()
    {
        $customerGroup = $this->helper->createCustomerGroup(array('key' => 'CON'));
        $second = $this->helper->createCustomerGroup(array('key' => 'CON2'));

        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getDefaultProduct('testSingleCustomerGroup-1', array($customerGroup), $category, $context),
            $this->getDefaultProduct('testSingleCustomerGroup-2', array($customerGroup), $category, $context),
            $this->getDefaultProduct('testSingleCustomerGroup-3', array($second), $category, $context),
            $this->getDefaultProduct('testSingleCustomerGroup-4', array(), $category, $context),
        );

        foreach($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addCustomerGroupCondition(array($customerGroup->getId(), $second->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testSingleCustomerGroup-4')
        );
    }
}