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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Providers;

use PHPUnit\Framework\Constraint\IsType;

class ProductsProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.products';
    const EXPECTED_KEYS_COUNT = 1;
    const EXPECTED_TYPES = [
        'list' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasic()
    {
        $this->installDemoData('products_basic');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertNotEmpty($productsList);

        static::assertEquals(0, $productsList[1]['active']);
        static::assertEquals(1, $productsList[2]['active']);
        static::assertEquals(8, $productsList[3]['instock']);
        static::assertEquals(20, $productsList[4]['instockMinimum']);
        static::assertEquals(0, $productsList[1]['sale']);
        static::assertEquals(1, $productsList[2]['sale']);
        static::assertEquals(2, $productsList[2]['minPurchase']);
        static::assertEquals(10, $productsList[3]['maxPurchase']);
        static::assertEquals(5, $productsList[4]['purchaseSteps']);
        static::assertEquals(0, $productsList[0]['shippingFree']);
        static::assertEquals(1, $productsList[1]['shippingFree']);
        static::assertEquals(10, $productsList[2]['pseudoSales']);
        static::assertEquals(0, $productsList[2]['topSeller']);
        static::assertEquals(1, $productsList[3]['topSeller']);
        static::assertEquals(0, $productsList[0]['notificationEnabled']);
        static::assertEquals(1, $productsList[1]['notificationEnabled']);
        static::assertEquals(7, $productsList[1]['shippingTime']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetVariants()
    {
        $this->installDemoData('products_variants');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertEquals(25, $productsList[4]['variants'][0]['instock']);
        static::assertEquals(30, $productsList[4]['variants'][1]['instock']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductImages()
    {
        $this->installDemoData('products_images');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertEquals(12345, $productsList[0]['images'][0]['fileSize']);
        static::assertEquals(54321, $productsList[0]['images'][1]['fileSize']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasicPerShop()
    {
        $this->installDemoData('products_basic');
        $this->installDemoData('second_config');

        $provider = $this->getProvider();
        $benchmarkData = $provider->getBenchmarkData(Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1));
        $productsList = $benchmarkData['list'];

        static::assertCount(5, $productsList);

        $benchmarkData = $provider->getBenchmarkData(Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(2));
        $productsList = $benchmarkData['list'];

        static::assertCount(1, $productsList);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasicConsidersBatchSize()
    {
        $this->resetConfig();
        $this->installDemoData('products_basic');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_product_id=4, batch_size=1;');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertCount(1, $productsList);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasicConsidersLastProductId()
    {
        $this->resetConfig();
        $this->installDemoData('products_basic');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_product_id=4, batch_size=1;');

        $benchmarkData = $this->getBenchmarkData();
        $productsList = $benchmarkData['list'];

        static::assertEquals(15, $productsList[0]['instock']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetProductListBasicUpdatesLastProductId()
    {
        $this->resetConfig();
        $this->installDemoData('products_basic');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_product_id=4, batch_size=1 WHERE shop_id=1;');

        $this->sendStatistics();

        static::assertEquals(5, Shopware()->Db()->fetchOne('SELECT last_product_id FROM s_benchmark_config WHERE shop_id=1'));
    }
}
