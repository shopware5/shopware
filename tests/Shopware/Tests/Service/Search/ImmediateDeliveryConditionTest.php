<?php

namespace Shopware\Tests\Service\Search;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Helper;

class ImmediateDeliveryConditionTest extends TestCase
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
     * @param \Shopware\Models\Category\Category $category
     * @param Context $context
     * @param array $data
     * @return array
     */
    private function getDefaultProduct(
        $number,
        Category $category,
        Context $context,
        $data = array('inStock' => 0, 'minPurchase' => 1)
    ) {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $product['lastStock'] = true;
        $product['mainDetail'] = array_merge($product['mainDetail'], $data);

        $product['categories'] = array(
            array('id' => $category->getId())
        );

        return $product;
    }

    public function testNoStock()
    {
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getDefaultProduct('testNoStock-1', $category, $context),
            $this->getDefaultProduct('testNoStock-2', $category, $context),
            $this->getDefaultProduct('testNoStock-3', $category, $context, array('inStock' => 2, 'minPurchase' => 1)),
            $this->getDefaultProduct('testNoStock-4', $category, $context, array('inStock' => 2, 'minPurchase' => 1)),
        );

        foreach($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addImmediateDeliveryCondition();


        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testNoStock-3', 'testNoStock-4')
        );
    }

    public function testMinPurchaseEquals()
    {
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getDefaultProduct('testMinPurchaseEquals-1', $category, $context),
            $this->getDefaultProduct('testMinPurchaseEquals-2', $category, $context),
            $this->getDefaultProduct('testMinPurchaseEquals-3', $category, $context, array('inStock' => 3, 'minPurchase' => 3)),
            $this->getDefaultProduct('testMinPurchaseEquals-4', $category, $context, array('inStock' => 20, 'minPurchase' => 20)),
        );

        foreach($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addImmediateDeliveryCondition();

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testMinPurchaseEquals-3', 'testMinPurchaseEquals-4')
        );
    }
}