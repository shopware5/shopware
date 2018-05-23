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

class PluginsProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.plugins';
    const EXPECTED_KEYS_COUNT = 4;
    const EXPECTED_TYPES = [
        'total' => IsType::TYPE_INT,
        'updateable' => IsType::TYPE_INT,
        'shopware' => IsType::TYPE_INT,
        'technical' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalPlugins()
    {
        $this->installDemoData('plugins');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(6, $resultData['total']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetUpdateablePlugins()
    {
        $this->installDemoData('plugins');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(2, $resultData['updateable']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetShopwarePlugins()
    {
        $this->installDemoData('plugins');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(4, $resultData['shopware']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetTechnicalPluginNames()
    {
        $this->installDemoData('plugins');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertArraySubset([
            'SwagExample1',
            'SwagExample2',
            'SwagExample3',
            'SwagExample4',
            'SwagExample5',
            'SwagExample6',
        ], $resultData['technical']);
    }
}
