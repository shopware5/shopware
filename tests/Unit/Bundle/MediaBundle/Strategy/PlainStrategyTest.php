<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Unit\Bundle\MediaBundle\Strategy;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\Strategy\PlainStrategy;

class PlainStrategyTest extends TestCase
{
    /**
     * @var PlainStrategy
     */
    private $strategy;

    protected function setUp(): void
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
     * @return array
     */
    public function getThumbnailEncodingPaths()
    {
        return [
            ['media/image/foo.jpg', 'media/image/foo.jpg'],
            ['media/image/foo_200x200.jpg', 'media/image/thumbnail/foo_200x200.jpg'],
            ['media/image/foo_200x200@2.jpg', 'media/image/thumbnail/foo_200x200@2.jpg'],
            ['media/image/200x200_foo.jpg', 'media/image/200x200_foo.jpg'],
            ['media/image/200x200@2_foo.jpg', 'media/image/200x200@2_foo.jpg'],
            ['media/wusel/200x200@2_foo.jpg', 'media/wusel/200x200@2_foo.jpg'],
        ];
    }

    /**
     * @dataProvider getNormalizedData
     *
     * @param string $path
     * @param string $expected
     */
    public function testNormalizer($path, $expected)
    {
        static::assertEquals(
            $expected,
            $this->strategy->normalize($path)
        );
    }

    public function testEncodedPath()
    {
        static::assertTrue($this->strategy->isEncoded('media/image/my-image.png'));
        static::assertTrue($this->strategy->isEncoded('http://www.shopware.com/media/image/my-image.png'));
    }

    public function testNotEncodedPath()
    {
        static::assertFalse($this->strategy->isEncoded('media/image/53/'));
        static::assertFalse($this->strategy->isEncoded('media/image/53/foo'));
        static::assertFalse($this->strategy->isEncoded('media/image/53/a4/d3/'));
        static::assertFalse($this->strategy->isEncoded('media/image/53/a4/d3/foo'));
        static::assertFalse($this->strategy->isEncoded('http://www.shopware.com/media/image/53/'));
    }

    /**
     * @dataProvider getInvalidPathsToEncode
     *
     * @param string $path
     */
    public function testEncodingWithInvalidPaths($path)
    {
        static::assertEquals('', $this->strategy->encode($path));
    }

    /**
     * @dataProvider getThumbnailEncodingPaths
     *
     * @param string $expectedPath
     * @param string $path
     */
    public function testEncodeForThumbnails($path, $expectedPath)
    {
        static::assertEquals($expectedPath, $this->strategy->encode($path));
    }
}
