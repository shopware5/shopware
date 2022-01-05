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

namespace Shopware\Tests\Functional\Plugins\Core\RestApi\Components;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Loader;
use PHPUnit\Framework\TestCase;
use ShopwarePlugins\RestApi\Components\Router;

class RouterTest extends TestCase
{
    private Router $router;

    public function setUp(): void
    {
        $helper = Shopware();
        $loader = $helper->Container()->get(Enlight_Loader::class);

        $pluginDir = $helper->DocPath() . 'engine/Shopware/Plugins/Default/Core/RestApi';

        $loader->registerNamespace('ShopwarePlugins\\RestApi\\Components', $pluginDir . '/Components/');

        $this->router = new Router();
    }

    public function testCanGetInstance(): void
    {
        static::assertInstanceOf(Router::class, $this->router);
    }

    /**
     * @return array<array<string|int|bool>>
     */
    public function routeGetProvider(): array
    {
        return [
            ['/api/articles/', 1, 'articles', 'index', false],
            ['/api/v1/articles/', 1, 'articles', 'index',  false],
            ['/api/v2/articles/', 2, 'articles', 'index', false],
            ['/api/articles/5', 1, 'articles', 'get', '5'],
            ['/api/articles/sw123', 1, 'articles', 'get', 'sw123'],
            ['/api/v1/articles/5', 1, 'articles', 'get', '5'],
            ['/api/v2/articles/5', 2, 'articles', 'get', '5'],
        ];
    }

    /**
     * @covers \ShopwarePlugins\RestApi\Components\Router::assembleRoute
     * @dataProvider routeGetProvider
     *
     * @param string|false $expectedId
     */
    public function testGetRoutes(string $uri, int $expectedVersion, string $expectedController, string $expectedAction, $expectedId): void
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setMethod('GET');

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $request->setPathInfo($uri);
        $this->router->assembleRoute($request, $response);

        static::assertSame($expectedController, $request->getControllerName());
        static::assertSame($expectedAction, $request->getActionName());
        static::assertSame($expectedVersion, $request->getParam('version'));
        static::assertSame($expectedId, $request->getParam('id'));
        static::assertSame(200, $response->getHttpResponseCode());
    }

    /**
     * @return array<array<string|int|bool>>
     */
    public function routePostProvider(): array
    {
        return [
            ['/api/articles/', 1, 'articles', 'post', false],
            ['/api/v1/articles/', 1, 'articles', 'post', false],
            ['/api/v2/articles/', 2, 'articles', 'post', false],
            ['/api/articles/5', 1, 'articles', 'post', '5'],
            ['/api/v1/articles/5', 1, 'articles', 'post', '5'],
            ['/api/v2/articles/5', 2, 'articles', 'post', '5'],
        ];
    }

    /**
     * @covers \ShopwarePlugins\RestApi\Components\Router::assembleRoute
     * @dataProvider routePostProvider
     *
     * @param string|false $expectedId
     */
    public function testPostRoutes(string $uri, int $expectedVersion, string $expectedController, string $expectedAction, $expectedId): void
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setMethod('POST');

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $request->setPathInfo($uri);
        $this->router->assembleRoute($request, $response);

        static::assertSame($expectedController, $request->getControllerName());
        static::assertSame($expectedAction, $request->getActionName());
        static::assertSame($expectedVersion, $request->getParam('version'));
        static::assertSame($expectedId, $request->getParam('id'));
    }

    /**
     * @return array<array<string|int|bool>>
     */
    public function routePutProvider(): array
    {
        return [
            ['/api/articles/', 1, 'articles', 'batch', false, 200],
            ['/api/v1/articles/', 1, 'articles', 'batch', false, 200],
            ['/api/v2/articles/', 2, 'articles', 'batch', false, 200],
            ['/api/articles/5', 1, 'articles', 'put', '5', 200],
            ['/api/v1/articles/5', 1, 'articles', 'put', '5', 200],
            ['/api/v2/articles/5', 2, 'articles', 'put', '5', 200],
        ];
    }

    /**
     * @covers \ShopwarePlugins\RestApi\Components\Router::assembleRoute
     * @dataProvider routePutProvider
     *
     * @param string|false $expectedId
     */
    public function testPutRoutes(string $uri, int $expectedVersion, string $expectedController, string $expectedAction, $expectedId, int $expectedCode): void
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setMethod('PUT');

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $request->setPathInfo($uri);
        $this->router->assembleRoute($request, $response);

        static::assertSame($expectedController, $request->getControllerName());
        static::assertSame($expectedAction, $request->getActionName());
        static::assertSame($expectedVersion, $request->getParam('version'));
        static::assertSame($expectedId, $request->getParam('id'));
        static::assertSame($expectedCode, $response->getHttpResponseCode());
    }

    /**
     * @return array<array<string|int|bool>>
     */
    public function routeDeleteProvider(): array
    {
        return [
            ['/api/articles/', 1, 'articles', 'batchDelete', false, 200],
            ['/api/v1/articles/', 1, 'articles', 'batchDelete', false, 200],
            ['/api/v2/articles/', 2, 'articles', 'batchDelete', false, 200],

            ['/api/articles/5', 1, 'articles', 'delete', '5', 200],
            ['/api/v1/articles/5', 1, 'articles', 'delete', '5', 200],
            ['/api/v2/articles/5', 2, 'articles', 'delete', '5', 200],
        ];
    }

    /**
     * @covers \ShopwarePlugins\RestApi\Components\Router::assembleRoute
     * @dataProvider routeDeleteProvider
     *
     * @param string|false $expectedId
     */
    public function testDeleteRoutes(string $uri, int $expectedVersion, string $expectedController, string $expectedAction, $expectedId, int $expectedCode): void
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setMethod('DELETE');

        $response = new Enlight_Controller_Response_ResponseTestCase();

        $request->setPathInfo($uri);
        $this->router->assembleRoute($request, $response);

        static::assertSame($expectedController, $request->getControllerName());
        static::assertSame($expectedAction, $request->getActionName());
        static::assertSame($expectedVersion, $request->getParam('version'));
        static::assertSame($expectedId, $request->getParam('id'));
        static::assertSame($expectedCode, $response->getHttpResponseCode());
    }
}
