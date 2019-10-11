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

class PluginsProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.plugins';
    const EXPECTED_KEYS_COUNT = 2;
    const EXPECTED_TYPES = [
        'total' => IsType::TYPE_INT,
        'shopwarePlugins' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalPlugins()
    {
        $this->installDemoData('plugins');

        $resultData = $this->getBenchmarkData();

        static::assertSame(6, $resultData['total']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetShopwarePlugins()
    {
        $this->installDemoData('plugins');

        $resultData = $this->getBenchmarkData();

        static::assertTrue(array_intersect([
            [
                'name' => 'SwagExample2',
                'active' => 0,
            ], [
                'name' => 'SwagExample3',
                'active' => 1,
            ], [
                'name' => 'SwagExample4',
                'active' => 1,
            ], [
                'name' => 'SwagExample5',
                'active' => 0,
            ],
        ], $resultData['shopwarePlugins']) === [
            [
                'name' => 'SwagExample2',
                'active' => 0,
            ], [
                'name' => 'SwagExample3',
                'active' => 1,
            ], [
                'name' => 'SwagExample4',
                'active' => 1,
            ], [
                'name' => 'SwagExample5',
                'active' => 0,
            ],
        ]);
    }
}
