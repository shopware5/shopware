<?php

class TermHelperTest extends Enlight_Components_Test_TestCase
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
}