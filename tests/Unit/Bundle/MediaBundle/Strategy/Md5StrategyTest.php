<?php

namespace Shopware\Tests\Bundle\MediaBundle\Strategy;

use Shopware\Bundle\MediaBundle\Strategy\Md5Strategy;

class Md5StrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Md5Strategy
     */
    private $strategy;

    protected function setUp()
    {
        $this->strategy = new Md5Strategy();
    }

    public function getNormalizData()
    {
        return [
            ['/media/image/Einkaufstasche.jpg', 'media/image/Einkaufstasche.jpg'],
            ['http://shopware.com/subfolder/shop/media/image/Einkaufstasche.jpg', 'media/image/Einkaufstasche.jpg'],
            ['/var/www/web1/shopware/media/image/Einkaufstasche.jpg', 'media/image/Einkaufstasche.jpg'],
        ];
    }

    /**
     * @dataProvider getNormalizData
     */
    public function testNormalizer($path, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->strategy->normalize($path)
        );
    }

    public function testEncodedPath()
    {
        $this->assertTrue($this->strategy->isEncoded('media/image/53/3d/af/my-image.png'));
        $this->assertTrue($this->strategy->isEncoded('http://www.shopware.com/media/image/53/3d/af/my-image.png'));
    }

    public function testNotEncodedPath()
    {
        $this->assertFalse($this->strategy->isEncoded('media/image/my-image.png'));
    }
}
