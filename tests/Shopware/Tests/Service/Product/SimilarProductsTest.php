<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Models\Article\Article;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;

class SimilarProductsTest extends \Enlight_Components_Test_TestCase
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
     * @param $number
     * @param Context $context
     * @return Article
     */
    private function createProduct($number, Context $context)
    {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        return $this->helper->createArticle($product);
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
     * @param $productId
     * @param $similarProductIds
     */
    private function linkSimilarProduct($productId, $similarProductIds)
    {
        foreach($similarProductIds as $similarProductId) {
            Shopware()->Db()->insert('s_articles_similar', array(
                'articleID' => $productId,
                'relatedarticle' => $similarProductId
            ));
        }
    }


    public function testSimilarProduct()
    {
        $context = $this->getContext();

        $number = 'testSimilarProduct';
        $article = $this->createProduct($number, $context);

        $similarNumbers = array();
        $similarProducts = array();
        for($i=0; $i<4; $i++) {
            $similarNumber = 'SimilarProduct-' . $i;
            $similarNumbers[] = $similarNumber;
            $similarProduct = $this->createProduct($similarNumber, $context);
            $similarProducts[] = $similarProduct->getId();
        }
        $this->linkSimilarProduct($article->getId(), $similarProducts);

        $product = Shopware()->Container()->get('list_product_service_core')
            ->get($number, $context);

        $similarProducts = Shopware()->Container()->get('similar_products_service_core')
            ->get($product, $context);

        $this->assertCount(4, $similarProducts);

        /**@var $similarProduct ListProduct*/
        foreach($similarProducts as $similarProduct) {
            $this->assertInstanceOf('\Shopware\Bundle\StoreFrontBundle\Struct\ListProduct', $similarProduct);
            $this->assertContains($similarProduct->getNumber(), $similarNumbers);
        }
    }


    public function testSimilarProductsList()
    {
        $context = $this->getContext();

        $number = 'testSimilarProductsList';
        $number2 = 'testSimilarProductsList2';

        $article = $this->createProduct($number, $context);
        $article2 = $this->createProduct($number2, $context);

        $similarNumbers = array();
        $similarProducts = array();
        for($i=0; $i<4; $i++) {
            $similarNumber = 'SimilarProduct-' . $i;
            $similarNumbers[] = $similarNumber;
            $similarProduct = $this->createProduct($similarNumber, $context);
            $similarProducts[] = $similarProduct->getId();
        }

        $this->linkSimilarProduct($article->getId(), $similarProducts);
        $this->linkSimilarProduct($article2->getId(), $similarProducts);

        $products = Shopware()->Container()->get('list_product_service_core')
            ->getList(array($number, $number2), $context);

        $similarProductList = Shopware()->Container()->get('similar_products_service_core')
            ->getList($products, $context);

        $this->assertCount(2, $similarProductList);

        foreach($products as $product) {
            $similarProducts = $similarProductList[$product->getNumber()];

            $this->assertCount(4, $similarProducts);

            /**@var $similarProduct ListProduct*/
            foreach($similarProducts as $similarProduct) {
                $this->assertInstanceOf('\Shopware\Bundle\StoreFrontBundle\Struct\ListProduct', $similarProduct);
                $this->assertContains($similarProduct->getNumber(), $similarNumbers);
            }
        }
    }

}
