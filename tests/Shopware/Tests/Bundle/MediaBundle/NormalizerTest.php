<?php

class TermHelperTest extends Enlight_Components_Test_TestCase
{
    public function testNormalizer()
    {
        /** @var \Shopware\Bundle\MediaBundle\MediaPathNormalizer $normalizer */
        $normalizer = Shopware()->Container()->get('shopware_media.path_normalizer');

        $result = $normalizer->get('/media/image/Einkaufstasche.jpg');
        $this->assertEquals('media/image/Einkaufstasche.jpg', $result);

        $result = $normalizer->get('http://shopware.com/subfolder/shop/media/image/Einkaufstasche.jpg');
        $this->assertEquals('media/image/Einkaufstasche.jpg', $result);

        $result = $normalizer->get('/var/www/web1/shopware/media/image/Einkaufstasche.jpg');
        $this->assertEquals('media/image/Einkaufstasche.jpg', $result);
    }
}