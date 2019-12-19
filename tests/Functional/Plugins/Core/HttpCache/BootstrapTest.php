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

namespace Shopware\Tests\Functional\Plugins\Core\HttpCache;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Components\CacheManager;
use Shopware\Components\HttpCache\DefaultCacheTimeService;
use Shopware\Components\HttpCache\DefaultRouteService;
use Shopware\Components\HttpCache\DynamicCacheTimeService;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use ShopwarePlugins\HttpCache\CacheControl;

class BootstrapTest extends \Enlight_Components_Test_Controller_TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var InstallerService
     */
    private $pluginManager;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function setUp(): void
    {
        $this->connection = Shopware()->Container()->get('dbal_connection');

        $this->pluginManager = Shopware()->Container()->get('shopware_plugininstaller.plugin_manager');

        $this->cacheManager = Shopware()->Container()->get('shopware.cache_manager');

        $plugin = $this->pluginManager->getPluginByName('HttpCache');

        $this->pluginManager->installPlugin($plugin);
        $this->pluginManager->activatePlugin($plugin);

        Shopware()->Container()->reset('plugins')->load('plugins');

        parent::setUp();
    }

    public function testCacheableRoute(): void
    {
        $this->resetHttpCache([
            'cacheControllers' => "frontend/index 100\r\n",
        ]);

        $this->Request()->setHeader('Surrogate-Capability', 'ESI/1.0');

        $response = $this->dispatch('/');

        static::assertSame(
            'max-age=100, public, s-maxage=100',
            $this->getHeader('Cache-Control', $response)
        );
    }

    public function testNotCacheableRoute(): void
    {
        $this->resetHttpCache([
            'cacheControllers' => "frontend/sitemap 500\r\n",
        ]);

        $this->Request()->setHeader('Surrogate-Capability', 'ESI/1.0');

        $response = $this->dispatch('/');

        $headers = array_column($response->getHeaders(), 'name');

        static::assertContains('Cache-Control', $headers);
        static::assertEquals('no-cache, private', $response->getHeader('cache-control'));
        static::assertNotContains('X-Shopware-Cache-Id', $headers);
    }

    public function testAdminSessionShouldNotBeCached(): void
    {
        $this->resetHttpCache([
            'cacheControllers' => "frontend/index 100\r\n",
        ]);

        Shopware()->Container()->get('session')->Admin = true;
        $this->Request()->setHeader('Surrogate-Capability', 'ESI/1.0');

        $response = $this->dispatch('/');

        $headers = array_column($response->getHeaders(), 'name');

        static::assertContains('Cache-Control', $headers);
        static::assertEquals('no-cache, private', $response->getHeader('cache-control'));
        static::assertNotContains('X-Shopware-Cache-Id', $headers);
        Shopware()->Container()->get('session')->Admin = false;
    }

    public function testNoCacheCookieForCacheableRoute(): void
    {
        $this->resetHttpCache([
            'cacheControllers' => "frontend/index 100\r\n",
            'noCacheControllers' => "frontend/index price\n",
        ]);

        $this->Request()->setCookie('nocache', 'price-1');
        $this->Request()->setHeader('Surrogate-Capability', 'ESI/1.0');

        $response = $this->dispatch('/');

        static::assertSame(
            'no-cache, private',
            $this->getHeader('Cache-Control', $response)
        );
    }

    public function testNoCacheCookieForCacheableRouteButWithOtherShop(): void
    {
        $this->resetHttpCache([
            'cacheControllers' => "frontend/index 100\r\n",
            'noCacheControllers' => "frontend/index price\n",
        ]);

        $this->Request()->setCookie('nocache', 'price-2');
        $this->Request()->setHeader('Surrogate-Capability', 'ESI/1.0');

        $response = $this->dispatch('/');

        static::assertSame(
            'max-age=100, public, s-maxage=100',
            $this->getHeader('Cache-Control', $response)
        );
    }

    public function testAddArticleAddsCheckoutNoCacheCookie(): void
    {
        $this->resetHttpCache([
            'cacheControllers' => "frontend/index 100\r\n",
            'noCacheControllers' => "frontend/index price\n",
        ]);

        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('Surrogate-Capability', 'ESI/1.0');

        $response = $this->dispatch('/checkout/ajaxAddArticleCart?sAdd=SW10178');

        static::assertSame('checkout-1', $this->getCookie($response, 'nocache'));
    }

    public function testClearBasketResetsNoCacheCookie(): void
    {
        $this->resetHttpCache([
            'cacheControllers' => "frontend/index 100\r\n",
            'noCacheControllers' => "frontend/index price\n",
        ]);

        $this->Request()->setMethod('POST');
        $this->Request()->setHeader('Surrogate-Capability', 'ESI/1.0');

        $response = $this->dispatch('/checkout/ajaxAddArticleCart?sAdd=SW10178');

        static::assertSame('checkout-1', $this->getCookie($response, 'nocache'));

        /** @var \Enlight_Components_Session_Namespace $session */
        $session = Shopware()->Container()->get('session');
        $session->offsetSet('sBasketQuantity', 0);
        $this->connection->executeUpdate('DELETE FROM s_order_basket');

        $response = $this->dispatch('/checkout/cart');
        static::assertSame('', $this->getCookie($response, 'nocache'));
    }

    private function getCookie(\Enlight_Controller_Response_Response $response, $name)
    {
        $cookies = $response->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie['name'] === $name) {
                return $cookie['value'];
            }
        }

        return null;
    }

    private function resetHttpCache($overrideConfig): void
    {
        $configReader = $this->createMock(CachedConfigReader::class);
        $configReader->method('getByPluginName')->willReturn($overrideConfig);

        $cacheRouteGeneration = Shopware()->Container()->get('shopware.http_cache.cache_route_generation_service');
        $defaultRouteService = new DefaultRouteService($configReader, $cacheRouteGeneration);
        $defaultCacheTimeService = new DefaultCacheTimeService($defaultRouteService);

        $invalidationDates = new \ArrayObject(
            [
                Shopware()->Container()->get('shopware.http_cache.invalidation_date.listing_date_frontend'),
                Shopware()->Container()->get('shopware.http_cache.invalidation_date.listing_date'),
                Shopware()->Container()->get('shopware.http_cache.invalidation_date.blog_date'),
                Shopware()->Container()->get('shopware.http_cache.invalidation_date.blog_listing'),
                Shopware()->Container()->get('shopware.http_cache.invalidation_date.product_date'),
            ]
        );

        $cacheTimeService = new DynamicCacheTimeService(
            $cacheRouteGeneration,
            $defaultCacheTimeService,
            $invalidationDates
        );

        Shopware()->Container()->set(
            'http_cache.cache_control',
            new CacheControl(
                Shopware()->Container()->get('session'),
                $overrideConfig,
                Shopware()->Container()->get('events'),
                $defaultRouteService,
                $cacheTimeService,
                $cacheRouteGeneration
            )
        );

        $this->cacheManager->clearHttpCache();
        $this->cacheManager->clearConfigCache();
    }

    private function getHeader($name, \Enlight_Controller_Response_Response $response)
    {
        foreach ($response->getHeaders() as $header) {
            if ($header['name'] === $name) {
                return $header['value'];
            }
        }

        return null;
    }
}
