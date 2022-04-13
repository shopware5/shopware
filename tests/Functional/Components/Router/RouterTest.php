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

namespace Shopware\Tests\Functional\Components\Router;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;

class RouterTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;

    /**
     * Tests if a generated SEO route is the same with or without the _seo parameters
     */
    public function testSeoRouteGeneration(): void
    {
        $router = $this->getRouter();
        $context = $this->createShopContext();

        $seo = $router->assemble(['controller' => 'detail', 'action' => 'index', 'sArticle' => 229], $context);
        $seoExplicit = $router->assemble(['controller' => 'detail', 'action' => 'index', 'sArticle' => 229, '_seo' => true], $context);

        static::assertSame($seo, $seoExplicit);
    }

    /**
     * Tests that the seo route generation can be deactivated
     */
    public function testDeactivatingSeoRouteGeneration(): void
    {
        $router = $this->getRouter();
        $context = $this->createShopContext();

        $seo = $router->assemble(['controller' => 'detail', 'action' => 'index', 'sArticle' => 229], $context);
        $seoExplicit = $router->assemble(['controller' => 'category', 'sCategory' => 11, '_seo' => false], $context);

        static::assertNotEquals($seo, $seoExplicit);
    }

    /**
     * Tests if a nonexisting seo route is the same with or without the _seo parameters
     */
    public function testNoneExistingSeoRouteGeneration(): void
    {
        $router = $this->getRouter();
        $context = $this->createShopContext();

        $seo = $router->assemble(['controller' => 'doesnotexist'], $context);
        $raw = $router->assemble(['controller' => 'doesnotexist', '_seo' => false], $context);

        static::assertSame($raw, $seo);

        $raw = $router->assemble(['controller' => 'doesnotexist', '_seo' => false], $context);
        $seo = $router->assemble(['controller' => 'doesnotexist', '_seo' => true], $context);

        static::assertSame($raw, $seo);
    }

    /**
     * Tests if the default action is being ignored
     */
    public function testDefaultActionDoesntMatter(): void
    {
        $router = $this->getRouter();
        $context = $this->createShopContext();

        $withAction = $router->assemble(['controller' => 'doesnotexist', 'action' => 'index'], $context);
        $withoutAction = $router->assemble(['controller' => 'doesnotexist'], $context);

        static::assertSame($withAction, $withoutAction);
    }

    /**
     * tests for complex arrays.
     *
     * @dataProvider getTestParamsProvider
     */
    public function testArrayParams(array $params): void
    {
        $router = $this->getRouter();

        $url = $router->assemble($params, $this->createShopContext());
        static::assertIsString($url);
        $match = $router->match($url);
        static::assertIsArray($match);
        static::assertEquals(array_intersect($match, $params), $params);
    }

    public function getTestParamsProvider(): array
    {
        return [
            [['foo' => 'bar']],
            [['foo' => ['bar' => 'baz']]],
            [['foo' => ['1', '2']]],
            [['foo' => ['1', '3' => '2', '2' => '1']]],
            [['param1', 'param2' => ['an', 'array']]],
            [[0 => 1, 'foo']],
            [['test' => ['often'], 'tests', 'moreTests' => 'value']],
            [['go' => ['deeper' => ['and' => ['even' => 'deeper']]]]],
        ];
    }

    public function testGenerateList(): void
    {
        $list = [
            4 => 'shopware.php?sViewport=cat&sCategory=5',
            5 => 'shopware.php?sViewport=cat&sCategory=9999',
            6 => 'shopware.php?sViewport=cat&sCategory=9',
        ];

        $urls = $this->getRouter()->generateList($list, $this->createShopContext());

        static::assertStringEndsWith('/genusswelten/', $urls[4]);
        static::assertStringEndsWith('/cat/index/sCategory/9999', $urls[5]);
        static::assertStringEndsWith('/freizeitwelten/', $urls[6]);
    }

    private function getRouter(): RouterInterface
    {
        $router = $this->getContainer()->get(RouterInterface::class);

        return clone $router;
    }

    private function createShopContext(): Context
    {
        $shop = $this->getContainer()->get('models')->find(Shop::class, 1);
        static::assertInstanceOf(Shop::class, $shop);

        return Context::createFromShop($shop, $this->getContainer()->get('config'));
    }
}
