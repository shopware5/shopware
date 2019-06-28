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

class AnalyticsProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.analytics';
    const EXPECTED_KEYS_COUNT = 2;
    const EXPECTED_TYPES = [
        'list' => IsType::TYPE_ARRAY,
        'listByDevice' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalVisits()
    {
        $this->installDemoData('analytics');

        $resultData = $this->getBenchmarkData();

        static::assertSame(26, array_sum(array_column($resultData['list'], 'totalUniqueVisits')));
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalVisitsByDevice()
    {
        $this->installDemoData('analytics');

        $resultData = $this->getBenchmarkData();

        $desktopRows = array_filter($resultData['listByDevice'], function ($value) {
            return $value['deviceType'] === 'desktop';
        });

        $mobileRows = array_filter($resultData['listByDevice'], function ($value) {
            return $value['deviceType'] === 'mobile';
        });

        static::assertSame(12, array_sum(array_column($desktopRows, 'totalUniqueVisits')));
        static::assertSame(14, array_sum(array_column($mobileRows, 'totalUniqueVisits')));
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalVisitsByShop()
    {
        $this->installDemoData('analytics');
        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(1));

        static::assertSame(26, array_sum(array_column($resultData['list'], 'totalUniqueVisits')));

        $resultData = $provider->getBenchmarkData($this->getShopContextByShopId(2));
        static::assertSame(466, array_sum(array_column($resultData['list'], 'totalUniqueVisits')));
    }
}
