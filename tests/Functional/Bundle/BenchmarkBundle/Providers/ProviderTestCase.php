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

abstract class ProviderTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BenchmarkProviderInterface
     */
    private $provider;

    public function setUp()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $dbalConnection->exec('START TRANSACTION;');
    }

    public function tearDown()
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $dbalConnection->exec('ROLLBACK;');
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetArrayKeysFit()
    {
        $provider = $this->getProvider();

        $resultData = $provider->getBenchmarkData();
        $arrayKeys = array_keys($resultData);

        $this->assertCount($this::EXPECTED_KEYS_COUNT, $arrayKeys);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetValidateTypes()
    {
        $provider = $this->getProvider();
        $resultData = $provider->getBenchmarkData();

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
     * @param string $dataName
     */
    protected function installDemoData($dataName)
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $fileContent = $this->openDemoDataFile($dataName);

        $dbalConnection->exec($fileContent);
    }

    /**
     * @param array $data
     * @param array $expectedTypes
     */
    protected function checkForTypes(array $data, array $expectedTypes)
    {
        //TODO: Find solution if no entry is in database, maybe example sql as requirement?
        foreach ($data as $resultKey => $resultItem) {
            if (is_array($expectedTypes[$resultKey])) {
                $this->checkForTypes($resultItem, $expectedTypes[$resultKey]);
                continue;
            }

            try {
                $this->assertInternalType(
                    $expectedTypes[$resultKey],
                    $resultItem,
                    'foo'
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

    /**
     * @param string $fileName
     *
     * @throws \Exception
     */
    private function openDemoDataFile($fileName)
    {
        $fileName .= '.sql';
        $path = __DIR__ . '/assets/';
        $filePath = $path . $fileName;
        if (!file_exists($filePath)) {
            throw new \Exception(sprintf('File with name %s does not exist in path %s', $fileName, $path));
        }

        return file_get_contents($filePath);
    }
}
