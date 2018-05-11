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

class AnalyticsProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.analytics';
    const EXPECTED_KEYS_COUNT = 6;
    const EXPECTED_TYPES = [
        'totalVisitsYesterday' => IsType::TYPE_INT,
        'totalViewsYesterday' => IsType::TYPE_INT,
        'visitsByDeviceYesterday' => IsType::TYPE_ARRAY,
        'totalVisitsByDevice' => IsType::TYPE_ARRAY,
        'totalVisits' => IsType::TYPE_INT,
        'averageShippingCostsPerOrder' => IsType::TYPE_FLOAT,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalVisits()
    {
        $this->installDemoData('analytics');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(492, $resultData['totalVisits']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalVisitsYesterday()
    {
        $this->installDemoData('analytics');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(25, $resultData['totalVisitsYesterday']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalViewsYesterday()
    {
        $this->installDemoData('analytics');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(16, $resultData['totalViewsYesterday']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalVisitsByDevice()
    {
        $this->installDemoData('analytics');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(12, $resultData['totalVisitsByDevice']['desktop']);
        $this->assertSame(480, $resultData['totalVisitsByDevice']['mobile']);
    }
}
