<?php

namespace Shopware\Tests\Service\Search;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Helper;

class ManufacturerConditionTest extends TestCase
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
     * @param Supplier $manufacturer
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Context $context
     * @return array
     */
    private function getDefaultProduct($number, Category $category, Supplier $manufacturer, Context $context)
    {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $product['categories'] = array(
            array('id' => $category->getId())
        );

        if ($manufacturer) {
            $product['supplierId'] = $manufacturer->getId();
        }

        return $product;
    }

    public function testSingleManufacturer()
    {
        $category = $this->helper->createCategory();
        $manufacturer = $this->helper->createManufacturer();

        $context = $this->getContext();

        $articles = array(
            $this->getDefaultProduct('testSingleManufacturer-1', $category, null, $context),
            $this->getDefaultProduct('testSingleManufacturer-2', $category, $manufacturer, $context),
        );

        foreach($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->category(array($category->getId()));
        $criteria->manufacturer(array($manufacturer->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testSingleManufacturer-2')
        );
    }

    public function testMultipleManufacturers()
    {
        $category = $this->helper->createCategory();
        $manufacturer = $this->helper->createManufacturer();
        $second = $this->helper->createManufacturer();

        $context = $this->getContext();

        $articles = array(
            $this->getDefaultProduct('testMultipleManufacturers-1', $category, null, $context),
            $this->getDefaultProduct('testMultipleManufacturers-2', $category, $manufacturer, $context),
            $this->getDefaultProduct('testMultipleManufacturers-3', $category, $second, $context),
        );

        foreach($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->category(array($category->getId()));
        $criteria->manufacturer(array($manufacturer->getId(), $second->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testMultipleManufacturers-2', 'testMultipleManufacturers-3')
        );
    }
}