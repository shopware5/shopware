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

use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Tests\Functional\Bundle\BenchmarkBundle\BenchmarkTestCase;

abstract class ProviderTestCase extends BenchmarkTestCase
{
    /**
     * @var BenchmarkProviderInterface
     */
    private $provider;

    /**
     * @group BenchmarkBundle
     */
    public function testGetArrayKeysFit()
    {
        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();
        $arrayKeys = array_keys($resultData);

        // Dynamic keys
        if ($this::EXPECTED_KEYS_COUNT === 'dynamic') {
            return;
        }

        $this->assertCount($this::EXPECTED_KEYS_COUNT, $arrayKeys);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetValidateTypes()
    {
        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

        if (!is_array($this::EXPECTED_TYPES)) {
            $this->assertInternalType($this::EXPECTED_TYPES, $resultData);

            return;
        }

        $this->checkForTypes($resultData, $this::EXPECTED_TYPES);
    }

    /**
     * @return BenchmarkProviderInterface
     */
    protected function getProvider()
    {
        if ($this->provider === null) {
            $this->provider = Shopware()->Container()->get($this::SERVICE_ID);
        }

        return $this->provider;
    }

    /**
     * @param array $data
     * @param array $expectedTypes
     */
    protected function checkForTypes(array $data, array $expectedTypes)
    {
        foreach ($data as $resultKey => $resultItem) {
            if (!$expectedTypes[$resultKey]) {
                continue;
            }

            if (is_array($expectedTypes[$resultKey])) {
                $this->checkForTypes($resultItem, $expectedTypes[$resultKey]);
                continue;
            }

            try {
                $this->assertInternalType(
                    $expectedTypes[$resultKey],
                    $resultItem
                );
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                // Print custom error message
                $this->fail(sprintf(
                    'Failed asserting that the value for the key %s is of type %s',
                    $resultKey,
                    $expectedTypes[$resultKey]
                ));
            }
        }
    }

    protected function getAssetsFolder()
    {
        return __DIR__ . '/assets/';
    }
}
