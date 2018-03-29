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

class LocalOrdersProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.local_orders';
    const EXPECTED_KEYS_COUNT = 'dynamic';

    // Starts dynamically on first layer already
    const EXPECTED_TYPES = IsType::TYPE_ARRAY;

    /**
     * @group BenchmarkBundle
     */
    public function testGetOrderDataByDay()
    {
        $this->installDemoData('local_orders');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        $this->assertCount(5, $resultData);
        $this->assertArraySubset([
            '2012-08-30',
            '2012-08-31',
            '2012-09-01',
            '2012-09-02',
            '2012-09-03',
        ], array_keys($resultData));

        $this->assertSame(300.0, $resultData['2012-08-30']['orderAmount']);
        $this->assertSame(170.0, $resultData['2012-08-30']['orderAmountNet']);
        $this->assertSame('Thursday', $resultData['2012-08-30']['dayOfWeek']);
        $this->assertSame(2, $resultData['2012-08-30']['totalOrders']);
    }
}
