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

namespace Shopware\Tests\Functional\Components;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopware\Components\OptinServiceInterface;
use stdClass;

class OptinServiceTest extends TestCase
{
    /**
     * @var OptinServiceInterface
     */
    private $service;

    protected function setUp(): void
    {
        Shopware()->Models()->getConnection()->beginTransaction();
        $this->service = Shopware()->Container()->get(\Shopware\Components\OptinServiceInterface::class);
    }

    protected function tearDown(): void
    {
        Shopware()->Models()->getConnection()->rollBack();
    }

    public function testRetriveData()
    {
        $expectedData = ['YAYYY'];

        $hash = $this->service->add('foo', 3600, $expectedData);

        static::assertEquals($expectedData, $this->service->get('foo', $hash));
        static::assertNotEquals($expectedData, $this->service->get('foo2', $hash));
    }

    public function testRetriveLeftData()
    {
        $expectedData = ['YAYYY'];

        $hash = $this->service->add('foo', -3600, $expectedData);

        static::assertNull($this->service->get('foo', $hash));
    }

    public function testAssertAdd()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$type has to be of type string');

        $this->service->add(new stdClass(), 300, []);
    }

    public function testAssertGet()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$type has to be of type string');

        $this->service->get(new stdClass(), 'asddassdasa');
    }

    public function testDelete()
    {
        $testData = ['foo' => 'yes'];
        $hash = $this->service->add('foo', 3600, $testData);
        static::assertEquals($testData, $this->service->get('foo', $hash));
        $this->service->delete('foo', $hash);
        static::assertNull($this->service->get('foo', $hash));
    }
}
