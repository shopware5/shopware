<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Unit\Bundle\BenchmarkBundle;

use Shopware\Bundle\BenchmarkBundle\BenchmarkCollector;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group BenchmarkBundle
     */
    public function testGet()
    {
        $provider = new BenchmarkCollector(new \ArrayObject([
            new ShopBenchmarkProvider(),
            new FooBenchmarkProvider(),
            new BarBenchmarkProvider(),
        ]));

        $result = $provider->get(new ShopContextMock());
        static::assertArrayHasKey('id', $result);
        static::assertArrayHasKey('foo', $result);
        static::assertArrayHasKey('bar', $result);
        static::assertSame(1, (int) $result['id']);
        static::assertSame([
            'foo' => 'bar',
            'john' => 'doe',
        ], $result['foo']);
        static::assertSame([
            'test' => 'example',
        ], $result['bar']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetShouldThrowExceptionNoShopProvider()
    {
        $provider = new BenchmarkCollector(new \ArrayObject([
            new FooBenchmarkProvider(),
            new BarBenchmarkProvider(),
        ]));

        $this->expectExceptionMessage('Necessary data with name \'shop\' not provided.');
        $provider->get(new ShopContextMock());
    }
}

class FooBenchmarkProvider implements BenchmarkProviderInterface
{
    public function getName()
    {
        return 'foo';
    }

    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        return [
            'foo' => 'bar',
            'john' => 'doe',
        ];
    }
}

class BarBenchmarkProvider implements BenchmarkProviderInterface
{
    public function getName()
    {
        return 'bar';
    }

    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        return [
            'test' => 'example',
        ];
    }
}

class ShopBenchmarkProvider implements BenchmarkProviderInterface
{
    public function getName()
    {
        return 'shop';
    }

    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        return [
            'id' => '1',
        ];
    }
}

class ShopContextMock extends ShopContext
{
    public function __construct()
    {
    }

    public function getShop()
    {
        return new ShopMock();
    }
}

class ShopMock
{
    public function getId()
    {
        return 1;
    }

    public function getCategory()
    {
        return new CategoryMock();
    }
}

class CategoryMock
{
    public function getId()
    {
        return 2;
    }
}
