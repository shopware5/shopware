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

namespace Shopware\Tests\Functional\Bundle\SitemapBundle\Provider;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SitemapBundle\Provider\CategoryUrlProvider as OriginalProvider;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Routing\Context;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CategoryUrlProviderTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function setUp(): void
    {
        $dbConnection = $this->getContainer()->get('dbal_connection');

        $dbConnection->exec('DELETE FROM s_categories');
        $sql = file_get_contents(__DIR__ . '/assets/categories.sql');
        static::assertIsString($sql);
        $dbConnection->exec($sql);

        $dbConnection->exec('DELETE FROM s_core_shops');
        $sql = file_get_contents(__DIR__ . '/assets/shops.sql');
        static::assertIsString($sql);
        $dbConnection->exec($sql);
    }

    public function testGetUrls(): void
    {
        $shop = $this->getContainer()->get('models')->getRepository(Shop::class)->getActiveById(1);
        static::assertNotNull($shop);

        $urls = $this->getCategoryUrlProvider()->getUrls($this->getRouterContext($shop), $this->getShopContext($shop));

        static::assertCount(9, $urls);
    }

    public function testGetUrlsConsidersParentCategory(): void
    {
        $shop = $this->getContainer()->get('models')->getRepository(Shop::class)->getActiveById(1);
        static::assertNotNull($shop);

        $urls = $this->getCategoryUrlProvider()->getUrls($this->getRouterContext($shop), $this->getShopContext($shop));

        $firstUrl = $urls[0];

        static::assertSame(3, $firstUrl->getIdentifier());
    }

    public function testGetUrlsWorksWithSubshops(): void
    {
        $shop = $this->getContainer()->get('models')->getRepository(Shop::class)->getActiveById(1);
        static::assertNotNull($shop);

        $categoryUrlProvider = $this->getCategoryUrlProvider();
        $urls = $categoryUrlProvider->getUrls($this->getRouterContext($shop), $this->getShopContext($shop));

        static::assertCount(9, $urls);

        $shop = $this->getContainer()->get('models')->getRepository(Shop::class)->getActiveById(2);
        static::assertNotNull($shop);

        $categoryUrlProvider = $this->getCategoryUrlProvider();
        $urls = $categoryUrlProvider->getUrls($this->getRouterContext($shop), $this->getShopContext($shop));

        static::assertCount(2, $urls);
    }

    private function getRouterContext(Shop $shop): Context
    {
        return Context::createFromShop($shop, $this->getContainer()->get('config'));
    }

    private function getShopContext(Shop $shop): ShopContextInterface
    {
        static::assertNotNull($shop->getCurrency());

        return $this->getContainer()->get('shopware_storefront.context_service')->createShopContext(
            $shop->getId(),
            $shop->getCurrency()->getId(),
            $shop->getCustomerGroup()->getKey()
        );
    }

    private function getCategoryUrlProvider(): OriginalProvider
    {
        return new OriginalProvider($this->getContainer()->get('models'), $this->getContainer()->get('router'));
    }
}
