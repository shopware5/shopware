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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Resource\Cache;

class CacheTest extends TestCase
{
    /**
     * @var Cache
     */
    protected $resource;

    protected function setUp(): void
    {
        parent::setUp();

        $httpCacheDir = Shopware()->Container()->getParameter('shopware.httpcache.cache_dir');
        $templateCacheDir = Shopware()->Container()->getParameter('shopware.template.cacheDir');

        @mkdir($httpCacheDir, 0777, true);
        @mkdir($templateCacheDir, 0777, true);
    }

    public function getResource(): Cache
    {
        return $this->resource;
    }

    /**
     * @return Cache
     */
    public function createResource()
    {
        $resource = new Cache();
        $resource->setContainer(Shopware()->Container());

        return $resource;
    }

    /**
     * Check if listing all caches works
     */
    public function testGetListShouldBeSuccessFull(): void
    {
        $caches = $this->getResource()->getList();

        static::assertCount(6, $caches['data']);
    }

    /**
     * Check if reading template cache infos works
     */
    public function testGetOneShouldBeSuccessFull(): void
    {
        $info = $this->getResource()->getOne('template');
        static::assertEquals('template', $info['id']);
    }

    /**
     * Check if clearing the template cache is successful
     */
    public function testClearTemplateCacheShouldBeSuccessFull(): void
    {
        $this->getResource()->delete('template');

        $info = $this->getResource()->getOne('template');
        static::assertFalse(isset($info['files']));
    }

    /**
     * Check if clearing the template cache is successful
     */
    public function testClearHttpCacheShouldBeSuccessFull(): void
    {
        $this->getResource()->delete('http');
        $info = $this->getResource()->getOne('http');
        static::assertFalse(isset($info['files']));

        $this->getResource()->delete('template');
        $info = $this->getResource()->getOne('template');
        static::assertFalse(isset($info['files']));
    }
}
