<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;

class ManufacturerTest extends \Enlight_Components_Test_TestCase
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

    public function testManufacturerList()
    {
        $ids = array();
        $context = $this->getContext();
        $manufacturer = $this->helper->createManufacturer(array(
            'name' => 'testManufacturerList-1',
            'image' => 'Manufacturer-Cover-1',
            'link' => 'www.google.de?manufacturer=1',
            'metaTitle' => 'Meta title',
            'description' => 'Lorem ipsum manufacturer',
            'attribute' => array('id' => 100)
        ));
        $ids[] = $manufacturer->getId();

        $manufacturer = $this->helper->createManufacturer(array(
            'name' => 'testManufacturerList-2',
            'image' => 'Manufacturer-Cover-2',
            'link' => 'www.google.de?manufacturer=2',
            'metaTitle' => 'Meta title',
            'description' => 'Lorem ipsum manufacturer',
            'attribute' => array('id' => 100)
        ));
        $ids[] = $manufacturer->getId();


        $manufacturer = $this->helper->createManufacturer(array(
            'name' => 'testManufacturerList-2',
            'image' => 'Manufacturer-Cover-2',
            'link' => 'www.google.de?manufacturer=2',
            'metaTitle' => 'Meta title',
            'description' => 'Lorem ipsum manufacturer',
            'attribute' => array('id' => 100)
        ));
        $ids[] = $manufacturer->getId();

        $manufacturers = Shopware()->Container()->get('manufacturer_service_core')
            ->getList($ids, $context);

        /**@var $manufacturer Manufacturer*/
        foreach($manufacturers as $key => $manufacturer) {
            $this->assertEquals($key, $manufacturer->getId());

            $this->assertNotEmpty($manufacturer->getName());
            $this->assertNotEmpty($manufacturer->getLink());
            $this->assertNotEmpty($manufacturer->getDescription());
            $this->assertNotEmpty($manufacturer->getMetaTitle());
            $this->assertNotEmpty($manufacturer->getCoverFile());

            $this->assertGreaterThanOrEqual(1, $manufacturer->getAttributes());
            $this->assertTrue($manufacturer->hasAttribute('core'));
        }
    }

}
