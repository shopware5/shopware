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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Shopware\Models\Shop\Shop;

class SitemapTest extends \Enlight_Components_Test_Controller_TestCase
{
    public static function tearDownAfterClass(): void
    {
        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerShop(Shopware()->Models()->getRepository(Shop::class)->getActiveDefault());
    }

    /**
     * @param int $shopId
     *
     * @dataProvider sitemapDataprovider
     */
    public function testIndex($shopId, array $sitemapData)
    {
        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerShop(Shopware()->Models()->getRepository(Shop::class)->find($shopId));

        $controller = $this->getController();
        $controller->indexAction();

        $sCategoryTree = $controller->View()->getAssign('sCategoryTree');

        static::assertEquals(200, $this->Response()->getHttpResponseCode());

        foreach ($sitemapData as $name => $elements) {
            $partialTree = array_filter($sCategoryTree, function (array $treeElement) use ($name) {
                return $treeElement['name'] === $name;
            });

            $partialTree = reset($partialTree)['sub'];
            $partialTreeNames = array_column($partialTree, 'name');

            foreach ($elements as $element) {
                static::assertContains($element, $partialTreeNames);
            }
        }
    }

    /**
     * @return array
     */
    public function sitemapDataprovider()
    {
        return [
            [
                1,
                [
                    'Genusswelten' => [
                        'Tees und Zubehör',
                        'Edelbrände',
                        'Köstlichkeiten',
                    ],
                    'SitemapStaticPages' => [
                        'Impressum',
                    ],
                ],
            ],
            [
                2,
                [
                    'Worlds of indulgence' => [
                        'Teas and Accessories',
                        'Brandies',
                        'Delights',
                    ],
                    'SitemapStaticPages' => [
                        'Imprint',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return \Shopware_Controllers_Frontend_Sitemap
     */
    private function getController()
    {
        /** @var \Shopware_Controllers_Frontend_Sitemap $controller */
        $controller = \Enlight_Class::Instance(\Shopware_Controllers_Frontend_Sitemap::class, [
            $this->Request(),
            $this->Response(),
        ]);

        $controller->setContainer(Shopware()->Container());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));

        return $controller;
    }
}
