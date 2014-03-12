<?php
/**
 * Shopware 4.0
 * Copyright © shopware AG
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

/**
 * Class Shopware_Tests_Components_PathResolverTest
 */
class Shopware_Tests_Components_Theme_PathResolverTest extends Shopware_Tests_Components_Theme_Base
{
    /**
     * @var \Shopware\Models\Shop\Repository
     */
    private $shopRepo;

    /**
     * @var \Shopware\Components\Theme\PathResolver
     */
    private $pathResolver;

    protected function setUp()
    {
        parent::setUp();

        $this->shopRepo = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');

        $this->pathResolver = Shopware()->Container()->get('theme_path_resolver');
    }


    public function testFiles()
    {
        $shops = $this->shopRepo->findAll();

        $timestamp = '200000';
        $rootDir = Shopware()->Container()->getParameter('kernel.root_dir');

        /**@var $shop \Shopware\Models\Shop\Shop*/
        foreach($shops as $shop) {

            $names = array('theme', 'plugin');

            foreach($names as $name) {
                $file = $this->pathResolver->buildCssPath($shop, $name, $timestamp);
                $expected = $rootDir . '/web/cache/' . $timestamp . '_' . $name . $shop->getId() . '.css';
                $this->assertEquals($expected, $file);


                //js file name test
                $file = $this->pathResolver->buildJsPath($shop, $name, $timestamp);
                $expected = $rootDir . '/web/cache/' . $timestamp . '_' . $name . $shop->getId() . '.js';
                $this->assertEquals($expected, $file);
            }
        }
    }
}