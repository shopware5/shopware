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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Enlight_Class;
use Enlight_Components_Test_Controller_TestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware_Controllers_Frontend_Sitemap;

class SitemapTest extends Enlight_Components_Test_Controller_TestCase
{
    use ContainerTrait;

    public static function tearDownAfterClass(): void
    {
        Shopware()->Container()->get(ShopRegistrationServiceInterface::class)->registerShop(Shopware()->Models()->getRepository(Shop::class)->getActiveDefault());
    }

    /**
     * @dataProvider sitemapDataprovider
     */
    public function testIndex(int $shopId, array $sitemapData): void
    {
        $shop = $this->getContainer()->get(ModelManager::class)->getRepository(Shop::class)->find($shopId);
        static::assertInstanceOf(Shop::class, $shop);
        $this->getContainer()->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

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

    public function sitemapDataprovider(): array
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

    private function getController(): Shopware_Controllers_Frontend_Sitemap
    {
        $controller = Enlight_Class::Instance(Shopware_Controllers_Frontend_Sitemap::class, [
            $this->Request(),
            $this->Response(),
        ]);
        static::assertInstanceOf(Shopware_Controllers_Frontend_Sitemap::class, $controller);

        $controller->setContainer($this->getContainer());
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        return $controller;
    }
}
