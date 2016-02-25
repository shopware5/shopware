<?php

namespace Shopware\Tests\Bundle\MediaBundle;

/**
 * Class NormalizerTest
 * @package Shopware\Tests\Bundle\MediaBundle
 */
class NormalizerTest extends \Enlight_Components_Test_TestCase
{
    public function testNormalizer()
    {
        /** @var \Shopware\Bundle\MediaBundle\MediaService $mediaService */
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        $result = $mediaService->normalize('/media/image/Einkaufstasche.jpg');
        $this->assertEquals('media/image/Einkaufstasche.jpg', $result);

        $result = $mediaService->normalize('http://shopware.com/subfolder/shop/media/image/Einkaufstasche.jpg');
        $this->assertEquals('media/image/Einkaufstasche.jpg', $result);

        $result = $mediaService->normalize('/var/www/web1/shopware/media/image/Einkaufstasche.jpg');
        $this->assertEquals('media/image/Einkaufstasche.jpg', $result);
    }

    public function testNormalizerCrosscheck()
    {
        /** @var \Shopware\Bundle\MediaBundle\MediaService $mediaService */
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        $this->assertTrue($mediaService->isEncoded('media/image/53/3d/af/my-image.png'));
        $this->assertTrue($mediaService->isEncoded('http://www.shopware.com/media/image/53/3d/af/my-image.png'));
        $this->assertFalse($mediaService->isEncoded('media/image/my-image.png'));
    }
}
