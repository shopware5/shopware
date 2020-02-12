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

namespace Shopware\Tests\Components\Router;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Routing\Context;

class RouterTest extends Enlight_Components_Test_TestCase
{
    /**
     * Tests if a generated SEO route is the same with or without the _seo parameters
     */
    public function testSeoRouteGeneration(): void
    {
        $router = Shopware()->Container()->get('router');
        $localRouter = clone $router;

        $context = new Context();
        $context->setShopId(1);
        $localRouter->setContext($context);

        $seo = $localRouter->assemble(['controller' => 'detail', 'action' => 'index', 'sArticle' => 229]);
        $seoExplicit = $localRouter->assemble(['controller' => 'detail', 'action' => 'index', 'sArticle' => 229, '_seo' => true]);

        static::assertEquals($seo, $seoExplicit);
    }

    /**
     * Tests that the seo route generation can be deactivated
     */
    public function testDeactivatingSeoRouteGeneration(): void
    {
        $router = Shopware()->Container()->get('router');
        $localRouter = clone $router;

        $context = new Context();
        $context->setShopId(1);
        $localRouter->setContext($context);

        $seo = $localRouter->assemble(['controller' => 'detail', 'action' => 'index', 'sArticle' => 229]);
        $seoExplicit = $localRouter->assemble(['controller' => 'category', 'sCategory' => 11, '_seo' => false]);

        static::assertNotEquals($seo, $seoExplicit);
    }

    /**
     * Tests if a nonexisting seo route is the same with or without the _seo parameters
     */
    public function testNoneExistingSeoRouteGeneration(): void
    {
        $router = Shopware()->Container()->get('router');
        $localRouter = clone $router;

        $context = new Context();
        $context->setShopId(1);
        $localRouter->setContext($context);

        $seo = $localRouter->assemble(['controller' => 'doesnotexist']);
        $raw = $localRouter->assemble(['controller' => 'doesnotexist', '_seo' => false]);

        static::assertEquals($raw, $seo);

        $raw = $localRouter->assemble(['controller' => 'doesnotexist', '_seo' => false]);
        $seo = $localRouter->assemble(['controller' => 'doesnotexist', '_seo' => true]);

        static::assertEquals($raw, $seo);
    }

    /**
     * Tests if the default action is being ignored
     */
    public function testDefaultActionDoesntMatter(): void
    {
        $router = Shopware()->Container()->get('router');
        $localRouter = clone $router;

        $context = new Context();
        $context->setShopId(1);
        $localRouter->setContext($context);

        $withAction = $localRouter->assemble(['controller' => 'doesnotexist', 'action' => 'index']);
        $withoutAction = $localRouter->assemble(['controller' => 'doesnotexist']);

        static::assertEquals($withAction, $withoutAction);
    }

    /**
     * tests for complex arrays.
     *
     * @dataProvider getTestParamsProvider
     */
    public function testArrayParams(array $params): void
    {
        $router = Shopware()->Container()->get('router');
        $localRouter = clone $router;

        $context = new Context();
        $context->setShopId(1);
        $localRouter->setContext($context);

        $url = $localRouter->assemble($params);
        $match = $localRouter->match($url);
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
}
