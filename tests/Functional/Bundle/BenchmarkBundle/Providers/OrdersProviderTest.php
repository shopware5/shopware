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

class OrdersProviderTest extends ProviderTestCase
{
    const SERVICE_ID = \Shopware\Bundle\BenchmarkBundle\Provider\OrdersProvider::class;
    const EXPECTED_KEYS_COUNT = 1;
    const EXPECTED_TYPES = [
        'list' => IsType::TYPE_ARRAY,
    ];

    public function testGetArrayKeysFit()
    {
        $dbalConnection = Shopware()->Container()->get(\Doctrine\DBAL\Connection::class);
        $basicContent = $this->openDemoDataFile('basic_setup');
        $dbalConnection->exec($basicContent);

        parent::testGetArrayKeysFit();
    }

    public function testGetValidateTypes()
    {
        $dbalConnection = Shopware()->Container()->get(\Doctrine\DBAL\Connection::class);
        $basicContent = $this->openDemoDataFile('basic_setup');
        $dbalConnection->exec($basicContent);

        parent::testGetValidateTypes();
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersList()
    {
        $this->resetConfig();
        $this->installDemoData('orders_detailed');

        $resultData = $this->getBenchmarkData();

        static::assertArraySubset([
            'orderId' => '1',
            'currency' => 'EUR',
            'shippingCosts' => '15',
            'customer' => [
                'registered' => 1,
                'birthYear' => 1993,
                'birthMonth' => 1,
                'gender' => 'male',
                'registerDate' => '2011-11-23',
                'hasNewsletter' => 0,
                'billing' => [
                    'country' => 'DE',
                ],
                'shipping' => [
                    'country' => 'GR',
                ],
            ],
            'analytics' => [
                'device' => 'mobile',
                'referer' => null,
            ],
            'shipment' => [
                'name' => 'others',
                'cost' => [
                    'minPrice' => 14.00,
                    'maxPrice' => 14.00,
                ],
            ],
            'payment' => [
                'name' => 'others',
                'cost' => [
                    'percentCosts' => 0,
                    'absoluteCosts' => 0,
                    'absoluteCostsPerCountry' => 'DE:4;AE:-1',
                ],
            ],
            'items' => [
                [
                    'detailId' => 206,
                    'unitPrice' => 150.00,
                    'totalPrice' => 150.00,
                    'amount' => 1,
                    'packUnit' => '',
                    'purchaseUnit' => '',
                ], [
                    'detailId' => 207,
                    'unitPrice' => 20.00,
                    'totalPrice' => 80.00,
                    'amount' => 4,
                    'packUnit' => null,
                    'purchaseUnit' => null,
                ],
            ],
        ], $resultData['list'][0]);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersListBatch()
    {
        $this->installDemoData('orders_detailed');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=0, batch_size=1;');
        $singleResult = $this->getBenchmarkData();

        static::assertCount(1, $singleResult['list']);

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=0, batch_size=5;');
        $multipleResults = $this->getBenchmarkData();

        static::assertCount(5, $multipleResults['list']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersListDateConsidered()
    {
        $this->installDemoData('orders_detailed');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=4, batch_size=1;');
        $resultData = $this->getBenchmarkData();

        static::assertSame(5, $resultData['list'][0]['orderId']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersListMultipleExecutionsFetchesNewOrders()
    {
        $this->installDemoData('orders_detailed');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=4, batch_size=1;');
        $firstResult = $this->getBenchmarkData();

        $this->sendStatistics();

        $secondResult = $this->getBenchmarkData();

        static::assertSame(5, $firstResult['list'][0]['orderId']);
        static::assertSame(6, $secondResult['list'][0]['orderId']);

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=4, batch_size=2;');
        $thirdResultSet = $this->getBenchmarkData();

        $this->sendStatistics();

        $forthResultSet = $this->getBenchmarkData();

        static::assertSame(5, $thirdResultSet['list'][0]['orderId']);
        static::assertSame(6, $thirdResultSet['list'][1]['orderId']);

        static::assertSame(7, $forthResultSet['list'][0]['orderId']);
        // Only seven results possible, no eighth result available
        static::assertEmpty($forthResultSet['list'][1]);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersListIdConfigGetsUpdated()
    {
        $this->installDemoData('orders_detailed');

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=4, batch_size=1 WHERE shop_id=1;');
        $this->sendStatistics();

        static::assertSame(5, (int) Shopware()->Db()->fetchOne('SELECT last_order_id FROM s_benchmark_config WHERE shop_id=1'));
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersListPerShop()
    {
        $this->installDemoData('orders_detailed');
        $this->installDemoData('second_config');
        $provider = $this->getProvider();

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=0, batch_size=10;');

        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(1));
        static::assertCount(7, $resultData['list']);

        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(2));
        static::assertCount(2, $resultData['list']);
    }
}
