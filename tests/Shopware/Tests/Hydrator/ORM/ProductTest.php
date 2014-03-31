<?php

class Shopware_Tests_Hydrator_ORM_ProductTest extends Enlight_Components_Test_TestCase
{
    public function testHydrateProductBaseData()
    {
        $data = array(
            'name' => 'Test Product',
            'number' => 'SW-200000',
            'supplier' => array(
                'name' => 'Test supplier'
            )
        );

        $product = Shopware()->Container()->get('product_hydrator')->hydrateMini($data);

        $this->assertEquals($data['name'], $product->getName());
        $this->assertEquals($data['number'], $product->getNumber());

        $this->assertInstanceOf('Shopware\Struct\ProductMini', $product);
        $this->assertInstanceOf('Shopware\Struct\Manufacturer', $product->getManufacturer());

        $this->assertEquals('Test supplier', $product->getManufacturer()->getName());
    }

    public function testMediaAssertion()
    {
        $data = array(
            'name' => 'Test Product',
            'number' => 'SW-200000',
            'images' => array(
                array(
                    'id' => 1,
                    'main' => true,
                    'media' => array(
                        'path' => 'media/image/test-1.jpg'
                    )
                ),
                array(
                    'id' => 2,
                    'main' => false,
                    'media' => array(
                        'path' => 'media/image/test-2.jpg'
                    )
                )
            )
        );

        $product = Shopware()->Container()->get('product_hydrator')->hydrateMini($data);
        $media = $product->getMedia();

        $this->assertCount(2, $media);

        $this->assertTrue($media[0]->getPreview());
        $this->assertFalse($media[1]->getPreview());
    }

}
