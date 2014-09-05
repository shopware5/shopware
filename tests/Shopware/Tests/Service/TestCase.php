<?php

namespace Shopware\Tests\Service;

use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\SearchProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;

class TestCase extends \Enlight_Components_Test_TestCase
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Converter
     */
    protected $converter;

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
     * @param ProductNumberSearchResult $result
     * @param $expectedNumbers
     */
    protected function assertSearchResult(
        ProductNumberSearchResult $result,
        $expectedNumbers
    ) {
        $this->assertCount(count($expectedNumbers), $result->getProducts());
        $this->assertEquals(count($expectedNumbers), $result->getTotalCount());

        foreach ($result->getProducts() as $product) {
            $this->assertContains(
                $product->getNumber(),
                $expectedNumbers
            );
        }
    }

    protected function assertSearchResultSorting(
        ProductNumberSearchResult $result,
        $expectedNumbers
    ) {
        $productResult = array_values($result->getProducts());

        /**@var $product SearchProduct*/
        foreach ($productResult as $index => $product) {
            $expectedProduct = $expectedNumbers[$index];

            $this->assertEquals(
                $expectedProduct,
                $product->getNumber(),
                sprintf(
                    'Expected %s at search result position %s, but got product %s',
                    $expectedProduct, $index, $product->getNumber()
                )
            );
        }
    }

    /**
     * @return ProductContext
     */
    protected function getContext()
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
     * @param Category $category
     * @param Context $context
     * @return array
     */
    protected function getProduct(
        $number,
        Context $context,
        Category $category = null
    ) {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        if ($category) {
            $product['categories'] = array(
                array('id' => $category->getId())
            );
        }

        return $product;
    }
}
