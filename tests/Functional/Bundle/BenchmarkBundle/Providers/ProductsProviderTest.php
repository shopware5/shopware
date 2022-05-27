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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Providers;

use PHPUnit\Framework\Constraint\IsType;
use Shopware\Bundle\BenchmarkBundle\Provider\ProductsProvider;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;

class ProductsProviderTest extends ProviderTestCase
{
    protected const SERVICE_ID = ProductsProvider::class;
    protected const EXPECTED_KEYS_COUNT = 1;
    protected const EXPECTED_TYPES = [
        'list' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasic(): void
    {
        $this->installDemoData('products_basic');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertNotEmpty($productsList);

        static::assertFalse($productsList[1]['active']);
        static::assertTrue($productsList[2]['active']);
        static::assertSame(8, $productsList[3]['instock']);
        static::assertSame(20, $productsList[4]['instockMinimum']);
        static::assertSame(0, $productsList[1]['sale']);
        static::assertSame(1, $productsList[2]['sale']);
        static::assertSame(2, $productsList[2]['minPurchase']);
        static::assertSame(10, $productsList[3]['maxPurchase']);
        static::assertSame(5, $productsList[4]['purchaseSteps']);
        static::assertFalse($productsList[0]['shippingFree']);
        static::assertTrue($productsList[1]['shippingFree']);
        static::assertSame(10, $productsList[2]['pseudoSales']);
        static::assertSame(0, $productsList[2]['topSeller']);
        static::assertSame(1, $productsList[3]['topSeller']);
        static::assertFalse($productsList[0]['notificationEnabled']);
        static::assertTrue($productsList[1]['notificationEnabled']);
        static::assertSame('7', $productsList[1]['shippingTime']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetVariants(): void
    {
        $this->installDemoData('products_variants');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertSame(25, $productsList[4]['variants'][0]['instock']);
        static::assertSame(30, $productsList[4]['variants'][1]['instock']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductImages(): void
    {
        $this->installDemoData('products_images');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertSame('12345', (string) $productsList[0]['images'][0]['fileSize']);
        static::assertSame('54321', (string) $productsList[0]['images'][1]['fileSize']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasicPerShop(): void
    {
        $this->installDemoData('products_basic');
        $this->installDemoData('second_config');

        $provider = $this->getProvider();
        $benchmarkData = $provider->getBenchmarkData($this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1));
        $productsList = $benchmarkData['list'];

        static::assertCount(5, $productsList);

        $benchmarkData = $provider->getBenchmarkData($this->getContainer()->get(ContextServiceInterface::class)->createShopContext(2));
        $productsList = $benchmarkData['list'];

        static::assertCount(1, $productsList);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasicConsidersBatchSize(): void
    {
        $this->resetConfig();
        $this->installDemoData('products_basic');

        $this->getContainer()->get('dbal_connection')->executeStatement('UPDATE `s_benchmark_config` SET last_product_id=4, batch_size=1;');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertCount(1, $productsList);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasicConsidersLastProductId(): void
    {
        $this->resetConfig();
        $this->installDemoData('products_basic');

        $this->getContainer()->get('dbal_connection')->executeStatement('UPDATE `s_benchmark_config` SET last_product_id=4, batch_size=1;');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertSame(15, $productsList[0]['instock']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasicUpdatesLastProductId(): void
    {
        $this->resetConfig();
        $this->installDemoData('products_basic');

        $this->getContainer()->get('dbal_connection')->executeStatement('UPDATE `s_benchmark_config` SET last_product_id=4, batch_size=1 WHERE shop_id=1;');

        $this->sendStatistics();

        static::assertSame('5', (string) $this->getContainer()->get('dbal_connection')->fetchOne('SELECT last_product_id FROM s_benchmark_config WHERE shop_id=1'));
    }
}
