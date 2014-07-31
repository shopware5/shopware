<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Download;
use Shopware\Models\Article\Article;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;

class DownloadTest extends \Enlight_Components_Test_TestCase
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
     * @param Context $context
     * @return Article
     */
    private function getDefaultProduct($number, Context $context)
    {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $product['downloads'] = array(
            array(
                'name' => 'first-download',
                'size' => 100,
                'file' => '/var/www/first.txt',
                'attribute' => array('id' => 20000)
            ),
            array(
                'name' => 'second-download',
                'size' => 200,
                'file' => '/var/www/second.txt',
                'attribute' => array('id' => 20000)
            )
        );

        return $this->helper->createArticle($product);
    }


    public function testSingleProduct()
    {
        $context = $this->getContext();
        $number = 'testSingleProduct';
        $this->getDefaultProduct($number, $context);

        $product = Shopware()->Container()->get('list_product_service_core')->get($number, $context);

        $downloads = Shopware()->Container()->get('product_download_service_core')->get($product, $context);

        $this->assertCount(2, $downloads);

        /**@var $download Download*/
        foreach($downloads as $download) {
            $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Download', $download);
            $this->assertContains($download->getFile(), array('/var/www/first.txt', '/var/www/second.txt'));
            $this->assertCount(1, $download->getAttributes());
            $this->assertTrue($download->hasAttribute('core'));
        }
    }

    public function testDownloadList()
    {
        $numbers = array('testDownloadList-1', 'testDownloadList-2');
        $context = $this->getContext();
        foreach($numbers as $number) {
            $this->getDefaultProduct($number, $context);
        }

        $products = Shopware()->Container()->get('list_product_service_core')
            ->getList($numbers, $context);

        $downloads = Shopware()->Container()->get('product_download_service_core')
            ->getList($products, $context);

        $this->assertCount(2, $downloads);

        foreach($downloads as $number => $productDownloads) {
            $this->assertContains($number, $numbers);
            $this->assertCount(2, $productDownloads);
        }

        foreach($numbers as $number) {
            $this->assertArrayHasKey($number, $downloads);
        }
    }
}
