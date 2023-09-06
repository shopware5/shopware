<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Unit\Components\LegacyRequestWrapper;

use Enlight_Controller_Request_RequestTestCase;
use PHPUnit\Framework\TestCase;
use sSystem;

class GetWrapperTest extends TestCase
{
    private Enlight_Controller_Request_RequestTestCase $request;

    private sSystem $system;

    public function setUp(): void
    {
        $this->request = new Enlight_Controller_Request_RequestTestCase();
        $this->system = new sSystem($this->request);
    }

    public function tearDown(): void
    {
        $this->request->clearAll();
    }

    public function testSet(): void
    {
        $this->system->_GET->offsetSet('foo', 'bar');
        static::assertEquals('bar', $this->request->getQuery('foo'));

        $this->system->_GET->offsetSet('foo', null);
        static::assertNull($this->request->getQuery('bar'));

        $this->system->_GET->offsetSet('foo', []);
        static::assertNull($this->request->getQuery('bar'));
        static::assertIsArray($this->request->getQuery('foo'));
    }

    public function testGet(): void
    {
        $this->request->setQuery('foo', 'bar');
        static::assertEquals('bar', $this->system->_GET->offsetGet('foo'));

        $this->request->setQuery('foo', null);
        static::assertNull($this->system->_GET->offsetGet('bar'));

        $this->request->setQuery('foo', []);
        static::assertNull($this->system->_GET->offsetGet('bar'));
        static::assertIsArray($this->system->_GET->offsetGet('foo'));
    }

    public function testUnset(): void
    {
        $this->system->_GET->offsetSet('foo', 'bar');
        static::assertEquals('bar', $this->request->getQuery('foo'));
        unset($this->system->_GET['foo']);
        static::assertNull($this->request->getQuery('foo'));
    }

    public function testSetAll(): void
    {
        $this->system->_GET->offsetSet('foo', 'bar');
        static::assertEquals('bar', $this->request->getQuery('foo'));

        $this->system->_GET = ['foo' => 'too'];
        static::assertNull($this->request->getQuery('bar'));
        static::assertEquals('too', $this->request->getQuery('foo'));
    }

    public function testToArray(): void
    {
        $this->request->setQuery('foo', 'bar');
        static::assertEquals(['foo' => 'bar'], $this->system->_GET->toArray());
    }
}
