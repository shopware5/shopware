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

class ShipmentsProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.shipments';
    const EXPECTED_KEYS_COUNT = 2;
    const EXPECTED_TYPES = [
        'shipments' => IsType::TYPE_ARRAY,
        'shipmentUsages' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetShipments()
    {
        $this->installDemoData('shipments');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        $this->assertArraySubset([
            'Example dispatch 1' => [
                'minPrice' => 1.00,
                'maxPrice' => 15.00,
            ],
            'Example dispatch 2' => [
                'minPrice' => 13.00,
                'maxPrice' => 13.00,
            ],
            'Example dispatch 3' => [
                'minPrice' => 14.00,
                'maxPrice' => 14.00,
            ],
            'Example dispatch 4' => [
                'minPrice' => 2.50,
                'maxPrice' => 10.00,
            ],
            'Example dispatch 5' => [
                'minPrice' => 15.00,
                'maxPrice' => 15.00,
            ],
        ], $resultData['shipments']);

        $this->assertSame(1.0, $resultData['shipments']['Example dispatch 1']['minPrice']);
    }
}
