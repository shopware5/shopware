<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Bundle\SitemapBundle\Provider;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SitemapBundle\Provider\CustomUrlProvider;
use Shopware\Bundle\SitemapBundle\Service\ConfigHandler;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService;
use Shopware\Components\Routing\Context;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class CustomUrlProviderTest extends TestCase
{
    use ContainerTrait;

    public function testGetUrlsReturnsNoUrls(): void
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        $shopContext = $this->getContextService()->createShopContext(1);

        static::assertSame([], $customUrlProvider->getUrls(new Context(), $shopContext));
    }

    public function testGetUrlsReturnsAllUrlsForShop(): void
    {
        $shopId = 1;

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([
                [
                    'url' => 'foo',
                    'lastMod' => '2022-06-13 11:07:47',
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => 2,
                ],
                [
                    'url' => 'bar',
                    'lastMod' => '2022-06-13 11:07:47',
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => $shopId,
                ],
            ]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        $shopContext = $this->getContextService()->createShopContext($shopId);

        $urls = $customUrlProvider->getUrls(new Context(), $shopContext);
        static::assertCount(1, $urls);

        static::assertSame(
            '<url><loc>bar</loc><lastmod>2022-06-13</lastmod><changefreq>weekly</changefreq><priority>0.5</priority></url>',
            (string) $urls[0]
        );
    }

    public function testGetUrlsReturnsAllUrlsForShopIdZero(): void
    {
        $shopId = 1;

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([
                [
                    'url' => 'foo',
                    'lastMod' => '2022-06-13 11:07:47',
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => 2,
                ],
                [
                    'url' => 'bar',
                    'lastMod' => '2022-06-13 11:07:47',
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => 0,
                ],
                [
                    'url' => 'fooBar',
                    'lastMod' => '2022-06-13 11:07:47',
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => 0,
                ],
            ]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        $shopContext = $this->getContextService()->createShopContext($shopId);

        $urls = $customUrlProvider->getUrls(new Context(), $shopContext);
        static::assertCount(2, $urls);
        static::assertSame('bar', $urls[0]->getLoc());
        static::assertSame('fooBar', $urls[1]->getLoc());
    }

    public function testGetUrlsReturnsNoUrlsWrongShopId(): void
    {
        $shopId = 1;

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([
                [
                    'url' => 'foo',
                    'lastMod' => '2022-06-13 11:07:47',
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => 2,
                ],
            ]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        $shopContext = $this->getContextService()->createShopContext($shopId);

        static::assertEmpty($customUrlProvider->getUrls(new Context(), $shopContext));
    }

    private function getCustomUrlProvider(ConfigHandler $configHandlerStub): CustomUrlProvider
    {
        return new CustomUrlProvider($configHandlerStub);
    }

    private function getContextService(): ContextService
    {
        return $this->getContainer()->get(ContextServiceInterface::class);
    }
}
