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

namespace Shopware\Tests\Functional\Bundle\SitemapBundle\Provider;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SitemapBundle\Provider\CustomUrlProvider;
use Shopware\Bundle\SitemapBundle\Service\ConfigHandler;
use Shopware\Components\Routing\Context;

class CustomUrlProviderTest extends TestCase
{
    public function testGetUrlsReturnsNoUrls()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        $shopContext = $this->getContextService()->createShopContext(1);

        static::assertSame([], $customUrlProvider->getUrls(new Context(), $shopContext));
    }

    public function testGetUrlsReturnsAllUrlsForShop()
    {
        $shopId = 1;

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([
                [
                    'url' => 'foo',
                    'lastMod' => 1546853378,
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => 2,
                ], [
                    'url' => 'bar',
                    'lastMod' => 1546853378,
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => $shopId,
                ],
            ]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        $shopContext = $this->getContextService()->createShopContext($shopId);

        static::assertCount(1, $customUrlProvider->getUrls(new Context(), $shopContext));
    }

    public function testGetUrlsReturnsAllUrlsForShopIdZero()
    {
        $shopId = 1;

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([
                [
                    'url' => 'foo',
                    'lastMod' => 1546853378,
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => 2,
                ], [
                    'url' => 'bar',
                    'lastMod' => 1546853378,
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => 0,
                ], [
                    'url' => 'fooBar',
                    'lastMod' => 1546853378,
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

    public function testGetUrlsReturnsNoUrlsWrongShopId()
    {
        $shopId = 1;

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([
                [
                    'url' => 'foo',
                    'lastMod' => 1546853378,
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'shopId' => 2,
                ],
            ]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        $shopContext = $this->getContextService()->createShopContext($shopId);

        static::assertEmpty($customUrlProvider->getUrls(new Context(), $shopContext));
    }

    private function getCustomUrlProvider(ConfigHandler $configHandlerStub)
    {
        return new CustomUrlProvider($configHandlerStub);
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Service\Core\ContextService
     */
    private function getContextService()
    {
        return Shopware()->Container()->get('shopware_storefront.context_service');
    }
}
