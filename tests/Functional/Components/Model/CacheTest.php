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

namespace Shopware\Tests\Functional\Components\Model;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\Cache;

class CacheTest extends TestCase
{
    /**
     * @var Cache
     */
    private $cache;

    protected function setUp(): void
    {
        $zendCache = new \Zend_Cache_Core(['automatic_serialization' => true]);
        $zendCache->setBackend(new \Zend_Cache_Backend_File());
        $this->cache = new Cache($zendCache, 'Shopware_Models', ['tag']);
    }

    public function testMissingTags(): void
    {
        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('This Adapter requires at least one tag to work correct');

        new Cache(new \Zend_Cache_Core(), 'Shopware_Models', []);
    }

    public function testCacheWrite(): void
    {
        $this->cache->save('someValue', time());

        static::assertTrue($this->cache->contains('someValue'));
    }

    public function testCacheDoesNotExists(): void
    {
        static::assertFalse($this->cache->contains('foobar' . time()));
    }

    public function testCacheDeletion(): void
    {
        $this->cache->save('random', time());
        static::assertTrue($this->cache->contains('random'));
        $this->cache->delete('random');

        static::assertFalse($this->cache->contains('random'));
    }

    public function testCacheFlush(): void
    {
        $this->cache->save('random', time());
        static::assertTrue($this->cache->contains('random'));
        $this->cache->flushAll();

        static::assertFalse($this->cache->contains('random'));
    }

    public function testCacheDeleteAll(): void
    {
        $this->cache->save('random', time());
        static::assertTrue($this->cache->contains('random'));
        $this->cache->deleteAll();

        static::assertFalse($this->cache->contains('random'));
    }
}
