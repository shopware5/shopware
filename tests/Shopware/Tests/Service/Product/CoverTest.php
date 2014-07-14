<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Models\Customer\Group;
use Shopware\Models\Tax\Tax;
use Shopware\Bundle\StoreFrontBundle\Service\Core\MediaService;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;

class CoverTest extends \Enlight_Components_Test_TestCase
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

    public function testProductWithOneImage()
    {
        $number = 'Cover-Test';
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();

        $data = $this->getDefaultProduct($number, 1, $tax, $customerGroup);
        $this->helper->createArticle($data);

        $context = $this->helper->createContext($customerGroup, $this->helper->getShop(), array($tax));
        $product = $this->helper->getListProduct($number, $context);

        $this->assertMediaFile('sasse-korn', $product->getCover());
    }

    public function testProductWithMultipleImages()
    {
        $number = 'Cover-Test-Multiple';
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();

        $data = $this->getDefaultProduct($number, 10, $tax, $customerGroup);
        $this->helper->createArticle($data);

        $context = $this->helper->createContext($customerGroup, $this->helper->getShop(), array($tax));
        $product = $this->helper->getListProduct($number, $context);

        $this->assertMediaFile('sasse-korn', $product->getCover());
    }

    public function testProductList()
    {
        $number = 'Cover-Test-Listing';
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();

        $product1 = $this->getDefaultProduct($number . '-1', 4, $tax, $customerGroup);
        $product2 = $this->getDefaultProduct($number . '-2', 4, $tax, $customerGroup);

        $product1['images'][0] = $this->helper->getImageData(
            'test-spachtelmasse.jpg',
            array('main' => 1)
        );

        $product2['images'][0] = $this->helper->getImageData(
            'sasse-korn.jpg',
            array('main' => 1)
        );

        $this->helper->createArticle($product1);
        $this->helper->createArticle($product2);

        $context = $this->helper->createContext($customerGroup, $this->helper->getShop(), array($tax));

        $products = $this->helper->getListProducts(
            array($number . '-1', $number . '-2'),
            $context
        );

        $this->assertCount(2, $products);

        foreach ($products as $product) {
            $expected = 'test-spachtelmasse';
            if ($product->getNumber() == $number . '-2') {
                $expected = 'sasse-korn';
            }

            $this->assertMediaFile($expected, $product->getCover());
        }
    }

    /**
     * Tests the variant images configuration.
     *
     * Following case:
     * - Variant 1 has a configured variant image: tests/Shopware/Tests/Service/fixtures/sasse-korn.jpg
     * - Variant 2 has even a configured variant image: tests/Shopware/Tests/Service/fixtures/bienen_teaser.jpg
     *
     * Expected:
     * - Each product variant use their own variant image as cover.
     *
     */
    public function testVariantImages()
    {
        $number = 'Variant-Cover-Test';
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();

        $data = $this->getVariantImageProduct($number, $tax, $customerGroup);
        $this->helper->createArticle($data);

        $context = $this->helper->createContext(
            $customerGroup,
            $this->helper->getShop(),
            array($tax)
        );

        $variants = $this->helper->getListProducts(
            array_column($data['variants'], 'number'),
            $context
        );

        foreach ($variants as $variant) {
            $expected = 'bienen_teaser';
            if ($variant->getNumber() == $data['variants'][0]['number']) {
                $expected = 'sasse-korn';
            }

            $this->assertMediaFile($expected, $variant->getCover());
        }
    }


    /**
     * Test the shopware configuration forceMainImageInListing
     *
     * Following case:
     * - Variant 1 & 2 has a configured variant image.
     * - forceMainImageInListing is set to true
     *
     * Excepted:
     * - Both variants has the preview image of the global product.
     */
    public function testForceMainImage()
    {
        $number = 'Force-Main-Cover-Test';
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();

        $data = $this->getVariantImageProduct($number, $tax, $customerGroup);
        $this->helper->createArticle($data);

        $context = $this->helper->createContext(
            $customerGroup,
            $this->helper->getShop(),
            array($tax)
        );

        $config = $this->getMockBuilder('\Shopware_Components_Config')
            ->disableOriginalConstructor()
            ->getMock();

        $config->expects($this->once())
            ->method('get')
            ->will($this->returnValue(true));

        $mediaService = new MediaService(
            Shopware()->Container()->get('product_media_gateway'),
            Shopware()->Container()->get('variant_media_gateway'),
            $config
        );

        $variants = $this->helper->getListProducts(
            array_column($data['variants'], 'number'),
            $context,
            null,
            null,
            null,
            null,
            $mediaService
        );

        foreach ($variants as $variant) {
            $this->assertMediaFile('sasse-korn', $variant->getCover());
        }
    }

    /**
     * Test for fallback product main image.
     *
     * Following case:
     * - Variant 1 has a configured variant image
     * - Variant 2 not
     *
     * Expected:
     * - Variant 1 cover => configured variant image
     * - Variant 2 cover => Main image of the product.
     *
     */
    public function testFallbackImage()
    {
        $number = 'Force-Main-Cover-Test';
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();

        $data = $this->getVariantImageProduct($number, $tax, $customerGroup);
        $data['variants'][0]['images'] = array();

        $this->helper->createArticle($data);

        $context = $this->helper->createContext(
            $customerGroup,
            $this->helper->getShop(),
            array($tax)
        );

        $variants = $this->helper->getListProducts(
            array_column($data['variants'], 'number'),
            $context
        );

        foreach ($variants as $variant) {
            $expected = 'bienen_teaser';
            if ($variant->getNumber() == $data['variants'][0]['number']) {
                $expected = 'sasse-korn';
            }

            $this->assertMediaFile($expected, $variant->getCover());
        }
    }

    private function assertMediaFile($expected, Struct\Media $media)
    {
        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Media', $media);
        $this->assertNotEmpty($media->getThumbnails());

        $matcher = $this->stringContains($expected);
        $matcher->evaluate($media->getFile());

        foreach ($media->getThumbnails() as $thumbnail) {
            $matcher->evaluate($thumbnail);
        }
    }

    private function getDefaultProduct($number, $imageCount, Tax $tax, Group $customerGroup)
    {
        $customerGroup = $this->converter->convertCustomerGroup($customerGroup);

        $data = $this->helper->getSimpleProduct(
            $number,
            $tax,
            $customerGroup
        );

        $data['images'][] = $this->helper->getImageData(
            'sasse-korn.jpg',
            array('main' => 1)
        );

        for ($i=0; $i < $imageCount - 2; $i++) {
            $data['images'][] = $this->helper->getImageData();
        }

        return $data;
    }

    private function getVariantImageProduct($number, Tax $tax, Group $customerGroup)
    {
        $data = $this->getDefaultProduct(
            $number,
            2,
            $tax,
            $customerGroup
        );


        $customerGroup = $this->converter->convertCustomerGroup($customerGroup);
        $data = array_merge(
            $data,
            $this->helper->getConfigurator(
                $customerGroup,
                $number,
                array('Farbe' => array('rot', 'gelb'))
            )
        );

        $data['variants'][0]['images'] = array($this->helper->getImageData('sasse-korn.jpg'));
        $data['variants'][1]['images'] = array($this->helper->getImageData('bienen_teaser.jpg'));

        return $data;
    }
}
