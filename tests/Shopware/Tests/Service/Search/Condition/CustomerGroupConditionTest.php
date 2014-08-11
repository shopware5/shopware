<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group;
use Shopware\Tests\Service\TestCase;

class CustomerGroupConditionTest extends TestCase
{
    /**
     * @param $number
     * @param Group[] $customerGroups
     * @param \Shopware\Models\Category\Category $category
     * @param Context $context
     * @return array
     */
    protected function getProduct(
        $number,
        Context $context,
        Category $category = null,
        array $customerGroups = array()
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
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getProduct('testSingleCustomerGroup-1', $context, $category, array($customerGroup)),
            $this->getProduct('testSingleCustomerGroup-2', $context, $category, array($customerGroup)),
            $this->getProduct('testSingleCustomerGroup-3', $context, $category, array()),
            $this->getProduct('testSingleCustomerGroup-4', $context, $category, array()),
        );

        foreach ($articles as $article) {
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
            $this->getProduct('testSingleCustomerGroup-1', $context, $category, array($customerGroup)),
            $this->getProduct('testSingleCustomerGroup-2', $context, $category, array($customerGroup)),
            $this->getProduct('testSingleCustomerGroup-3', $context, $category, array($second)),
            $this->getProduct('testSingleCustomerGroup-4', $context, $category, array()),
        );

        foreach ($articles as $article) {
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
