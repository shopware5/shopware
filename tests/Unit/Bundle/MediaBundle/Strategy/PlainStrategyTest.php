<?php

namespace Shopware\Tests\Unit\Bundle\MediaBundle\Strategy;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\Strategy\PlainStrategy;

class PlainStrategyTest extends TestCase
{
    /**
     * @var PlainStrategy
     */
    private $strategy;

    protected function setUp()
    {
        $this->strategy = new PlainStrategy();
    }

    /**
     * @return array
     */
    public function getNormalizedData()
    {
        return [
            ['/media/image/Einkaufstasche.jpg', 'media/image/Einkaufstasche.jpg'],
            ['http://shopware.com/subfolder/shop/media/image/Einkaufstasche.jpg', 'media/image/Einkaufstasche.jpg'],
            ['/var/www/web1/shopware/media/image/Einkaufstasche.jpg', 'media/image/Einkaufstasche.jpg'],
            ['/var/www/web1/shopware/media/image/', '/var/www/web1/shopware/media/image/'],
            ['/var/www/web1/shopware/media/image/53/a4/d3/foo.jpg', 'media/image/foo.jpg'],
        ];
    }

    /**
     * @return array
     */
    public function getInvalidPathsToEncode()
    {
        return [
            ['media/image/53/'],
            ['media/image/53/fa'],
            ['media/image/53/fa/a3/'],
            ['media/image/53/fa/a3/foo'],
            ['media/image/53/fa/a3/foo.'],
            ['www.shopware.com/media/image/53/'],
            ['/var/www/local/media/image/53/'],
        ];
    }

    /**
     * @dataProvider getNormalizedData
     * @param string $path
     * @param string $expected
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
        $this->assertTrue($this->strategy->isEncoded('media/image/my-image.png'));
        $this->assertTrue($this->strategy->isEncoded('http://www.shopware.com/media/image/my-image.png'));
    }

    public function testNotEncodedPath()
    {
        $this->assertFalse($this->strategy->isEncoded('media/image/53/'));
        $this->assertFalse($this->strategy->isEncoded('media/image/53/foo'));
        $this->assertFalse($this->strategy->isEncoded('media/image/53/a4/d3/'));
        $this->assertFalse($this->strategy->isEncoded('media/image/53/a4/d3/foo'));
        $this->assertFalse($this->strategy->isEncoded('http://www.shopware.com/media/image/53/'));
    }

    /**
     * @dataProvider getInvalidPathsToEncode
     * @param string $path
     */
    public function testEncodingWithInvalidPaths($path)
    {
        $this->assertEquals("", $this->strategy->encode($path));
    }
}
