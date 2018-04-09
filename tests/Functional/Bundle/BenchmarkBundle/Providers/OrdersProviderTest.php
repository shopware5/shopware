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

use PHPUnit_Framework_Constraint_IsType as IsType;

class OrdersProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.orders';
    const EXPECTED_KEYS_COUNT = 3;
    const EXPECTED_TYPES = [
        'dateTime' => IsType::TYPE_STRING,
        'numbers' => [
            'total' => IsType::TYPE_INT,
            'revenue' => IsType::TYPE_ARRAY,
        ],
        'list' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalOrders()
    {
        $this->installDemoData('orders_basic');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(8, $resultData['numbers']['total']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersRevenue()
    {
        $this->installDemoData('orders_basic');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(1950.00, $resultData['numbers']['revenue']['totalGross']);
        $this->assertSame(520.00, $resultData['numbers']['revenue']['totalNet']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersList()
    {
        $this->installDemoData('orders_detailed');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertArraySubset([
            'orderId' => '1',
            'datetime' => '2012-08-30 10:15:00',
            'analytics' => [
                'device' => 'mobile',
                'referer' => null,
            ],
            'shipment' => [
                'name' => 'Example dispatch 3',
                'cost' => [
                    'minPrice' => 14.00,
                    'maxPrice' => 14.00,
                ],
            ],
            'payment' => [
                'name' => 'example4',
                'cost' => [
                    'percentCosts' => 0,
                    'absoluteCosts' => 0,
                    'absoluteCostsPerCountry' => 'DE:4;AE:-1',
                ],
            ],
            'items' => [
                [
                    'ean' => md5('example_ean'),
                    'unitPrice' => 150.00,
                    'totalPrice' => 150.00,
                    'amount' => 1,
                    'packUnit' => '',
                    'purchaseUnit' => '',
                ], [
                    'ean' => md5('SW10011'),
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

        $provider = $this->getProvider();

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=0, orders_batch_size=1;');
        $singleResult = $provider->getBenchmarkData();

        $this->assertCount(1, $singleResult['list']);

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=0, orders_batch_size=5;');
        $multipleResults = $provider->getBenchmarkData();

        $this->assertCount(5, $multipleResults['list']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersListDateConsidered()
    {
        $this->installDemoData('orders_detailed');

        $provider = $this->getProvider();

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=4, orders_batch_size=1;');
        $resultData = $provider->getBenchmarkData();

        $this->assertSame(5, $resultData['list'][0]['orderId']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersListMultipleExecutionsFetchesNewOrders()
    {
        $this->installDemoData('orders_detailed');

        $provider = $this->getProvider();

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=4, orders_batch_size=1;');
        $firstResult = $provider->getBenchmarkData();
        $secondResult = $provider->getBenchmarkData();

        $this->assertSame(5, $firstResult['list'][0]['orderId']);
        $this->assertSame(6, $secondResult['list'][0]['orderId']);

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=4, orders_batch_size=2;');
        $thirdResultSet = $provider->getBenchmarkData();
        $forthResultSet = $provider->getBenchmarkData();

        $this->assertSame(5, $thirdResultSet['list'][0]['orderId']);
        $this->assertSame(6, $thirdResultSet['list'][1]['orderId']);

        $this->assertSame(7, $forthResultSet['list'][0]['orderId']);
        $this->assertSame(8, $forthResultSet['list'][1]['orderId']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrdersListDateConfigGetsUpdated()
    {
        $this->installDemoData('orders_detailed');

        $provider = $this->getProvider();

        Shopware()->Db()->exec('UPDATE `s_benchmark_config` SET last_order_id=4, orders_batch_size=1;');
        $provider->getBenchmarkData();

        $this->assertSame(5, (int) Shopware()->Db()->fetchOne('SELECT last_order_id FROM s_benchmark_config'));
    }
}
