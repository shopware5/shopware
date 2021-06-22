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

namespace Shopware\Tests\Functional\Bundle\OrderBundle;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\OrderBundle\Service\OrderListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;

class OrderListProductService extends TestCase
{
    public function testGetAdditionalDetails(): void
    {
        $productNumbers = ['SW10002.3', 'SW10161.1', 'SW10011', 'SW10012', 'SW10123.1', 'SW10214.1', 'SW10002.2'];

        /** @var ContextServiceInterface $context */
        $context = Shopware()->Container()->get('shopware_storefront.context_service');

        /** @var OrderListProductServiceInterface $productService */
        $productService = Shopware()->Container()->get(OrderListProductServiceInterface::class);

        // Product service should always return variant images
        Shopware()->Config()->offsetSet('forceArticleMainImageInListing', 1);

        $result = $productService->getList($productNumbers, $context->getShopContext());

        static::assertCount(\count($productNumbers), $result);

        static::assertEquals($productNumbers, array_keys($result));

        static::assertEquals($productNumbers, array_column($result, 'ordernumber'));

        $properties = [
            'Trinktemperatur:&nbsp;Zimmertemperatur,&nbsp;Geschmack:&nbsp;mild,&nbsp;Farbe:&nbsp;goldig,&nbsp;Flaschengröße:&nbsp;0,5 Liter, 1,5 Liter, 5,0 Liter,&nbsp;Alkoholgehalt:&nbsp;>30%',
            'Geschmack:&nbsp;herb,&nbsp;Farbe:&nbsp;klar,&nbsp;Alkoholgehalt:&nbsp;>30%',
            'Trinktemperatur:&nbsp;Gekühlt,&nbsp;Geschmack:&nbsp;herb,&nbsp;Farbe:&nbsp;klar,&nbsp;Flaschengröße:&nbsp;0,2 Liter, 0,7 Liter, 1,0 Liter, 1,5 Liter,&nbsp;Alkoholgehalt:&nbsp;>30%',
            'Trinktemperatur:&nbsp;Zimmertemperatur,&nbsp;Geschmack:&nbsp;mild,&nbsp;Farbe:&nbsp;goldig,&nbsp;Flaschengröße:&nbsp;0,5 Liter, 1,5 Liter, 5,0 Liter,&nbsp;Alkoholgehalt:&nbsp;>30%',
        ];

        static::assertEquals($properties, array_column($result, 'properties'));

        $testImages = [
            'http://localhost/media/image/8b/7f/29/Muensterlaender_Lagerkorn.jpg',
            'http://localhost/media/image/a8/90/9d/Sandale-Beach-braun.jpg',
            'http://localhost/media/image/13/a8/09/Muensterlaender_Aperitif-Box.jpg',
            'http://localhost/media/image/fb/49/18/KobraVodka.jpg',
            'http://localhost/media/image/f6/b8/72/Sasse-Korn-02-l.jpg',
            'http://localhost/media/image/ba/fe/f0/All-Natural-Sesame-Sage-Body-Lotion.jpg',
            'http://localhost/media/image/58/ec/95/Lagerkorn-5l-Ballon502e4d0dcef95.jpg',
        ];
        $shop = Shopware()->Shop();
        $link = 'http' . ($shop->getSecure() ? 's' : '') . '://' . $shop->getHost() . $shop->getBasePath();
        $testImages = array_map(function ($image) use ($link) {
            return str_replace('http://localhost', $link, $image);
        }, $testImages);

        $images = array_column($result, 'image');
        $images = array_column($images, 'source');

        static::assertEquals($testImages, $images);
    }

    public function testEqualsToCoreClass(): void
    {
        $someProductNumber = Shopware()->Container()->get('dbal_connection')->fetchColumn('SELECT ordernumber FROM s_articles_details WHERE active = 1 LIMIT 1');

        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getContext();

        $productData = Shopware()->Container()->get('modules')->Articles()->sGetPromotionById('fix', 0, $someProductNumber);
        $products = Shopware()->Container()->get(OrderListProductServiceInterface::class)->getList([$someProductNumber], $context);
        $newProduct = array_shift($products);

        // attributes contains object. assertSame shows is that the same instance. Also properties is a new key. It's okay
        unset($newProduct['attributes'], $productData['attributes'], $newProduct['properties']);

        static::assertSame($productData, $newProduct);
    }
}
