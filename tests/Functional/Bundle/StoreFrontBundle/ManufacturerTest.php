<?php

namespace Shopware\Tests\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;


class ManufacturerTest extends TestCase
{
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

        $manufacturers = Shopware()->Container()->get('shopware_storefront.manufacturer_service')
            ->getList($ids, $context);

        /**@var $manufacturer Manufacturer*/
        foreach ($manufacturers as $key => $manufacturer) {
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
