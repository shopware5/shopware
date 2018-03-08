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

class EmotionsProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.emotions';
    const EXPECTED_KEYS_COUNT = 5;
    const EXPECTED_TYPES = [
        'total' => IsType::TYPE_INT,
        'landingPages' => IsType::TYPE_INT,
        'timed' => IsType::TYPE_INT,
        'elementUsages' => IsType::TYPE_ARRAY,
        'viewportUsages' => IsType::TYPE_ARRAY,
    ];

    /**
     * @group BenchmarkBundle
     */
    public function testGetTotalEmotions()
    {
        $this->installDemoData('emotions');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(6, $resultData['total']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetTimedEmotions()
    {
        $this->installDemoData('emotions');

        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();

        $this->assertSame(2, $resultData['timed']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetLandingPageEmotions()
    {
        $this->installDemoData('emotions');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        $this->assertSame(3, $resultData['landingPages']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetElementUsages()
    {
        $this->installDemoData('emotion_elements');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        $this->assertArraySubset([
            ['elementCount' => 3, 'elementName' => 'example-element-1'],
            ['elementCount' => 2, 'elementName' => 'example-element-2'],
        ], $resultData['elementUsages']);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetViewportUsages()
    {
        $this->installDemoData('emotions');

        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        $this->assertArraySubset(['6', '4', '6', '5', '6'], $resultData['viewportUsages']);
    }
}
