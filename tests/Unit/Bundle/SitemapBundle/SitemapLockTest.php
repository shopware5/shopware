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

namespace Shopware\Tests\Unit\Bundle\SitemapBundle;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SitemapBundle\Service\SitemapLock;
use Shopware\Bundle\StoreFrontBundle\Service\Core\CoreCache;
use Shopware\Models\Shop\Shop;

class SitemapLockTest extends TestCase
{
    /**
     * @var CoreCache&MockObject
     */
    private CoreCache $cacheMock;

    private int $lifeTime;

    public function setUp(): void
    {
        parent::setUp();

        $this->lifeTime = 3600;

        $this->cacheMock = $this->getMockBuilder(CoreCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheMock->method('fetch')
            ->with('sitemap-exporter-running-1')
            ->willReturn(false);
    }

    public function testAcquireLockDefaultFalse(): void
    {
        $shop = new ShopMock();
        $shop->id = 1;
        $lock = new SitemapLock($this->cacheMock, 'sitemap-exporter-running-%s');

        static::assertFalse($lock->isLocked($shop));
    }

    public function testAcquireLockWorks(): void
    {
        $data = sprintf('Locked: %s', (new DateTime('NOW', new DateTimeZone('UTC')))->format(DateTimeInterface::ATOM));
        $this->cacheMock->method('save')
            ->with('sitemap-exporter-running-1', $data, $this->lifeTime)
            ->willReturn(false);

        $shop = new ShopMock();
        $shop->id = 1;

        $lock = new SitemapLock($this->cacheMock, 'sitemap-exporter-running-%s');

        static::assertFalse($lock->isLocked($shop));
        static::assertTrue($lock->doLock($shop, $this->lifeTime));
    }
}

class ShopMock extends Shop
{
    /**
     * @var int
     */
    public $id;
}
