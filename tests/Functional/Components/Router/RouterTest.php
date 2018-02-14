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

class Shopware_Tests_Components_Router_RouterTest extends Enlight_Components_Test_TestCase
{
    /**
     * Tests if a generated SEO route is the same with or without the _seo parameters
     */
    public function testSeoRouteGeneration()
    {
        $router = Shopware()->Container()->get('router');

        $seo = $router->assemble(['controller' => 'register']);
        $seoExplicit = $router->assemble(['controller' => 'register', '_seo' => true]);

        $this->assertEquals($seo, $seoExplicit);

        $seo = $router->assemble(['controller' => 'register', '_seo' => false]);
        $seoExplicit = $router->assemble(['controller' => 'register', '_seo' => true]);

        $this->assertNotEquals($seo, $seoExplicit);
    }

    /**
     * Tests if a nonexisting seo route is the same with or without the _seo parameters
     */
    public function testNoneExistingSeoRouteGeneration()
    {
        $router = Shopware()->Container()->get('router');

        $seo = $router->assemble(['controller' => 'registerare']);
        $raw = $router->assemble(['controller' => 'registerare', '_seo' => false]);

        $this->assertEquals($raw, $seo);

        $raw = $router->assemble(['controller' => 'registerare', '_seo' => false]);
        $seo = $router->assemble(['controller' => 'registerare', '_seo' => true]);

        $this->assertEquals($raw, $seo);
    }
}
