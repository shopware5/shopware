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

        $result = $provider->get();

        $this->assertSame('{"id":"1","foo":{"foo":"bar","john":"doe"},"bar":{"test":"example"}}', $result);
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
        $provider->get();
    }
}

class FooBenchmarkProvider implements BenchmarkProviderInterface
{
    public function getName()
    {
        return 'foo';
    }

    public function getBenchmarkData()
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

    public function getBenchmarkData()
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

    public function getBenchmarkData()
    {
        return [
            'id' => '1',
        ];
    }
}
