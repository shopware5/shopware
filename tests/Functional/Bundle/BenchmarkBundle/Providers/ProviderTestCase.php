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

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Constraint\IsType;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

abstract class ProviderTestCase extends \Shopware\Tests\Functional\Bundle\BenchmarkBundle\BenchmarkTestCase
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
        $resultData = $this->getBenchmarkData();
        $arrayKeys = array_keys($resultData);
        static::assertCount($this::EXPECTED_KEYS_COUNT, $arrayKeys);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetValidateTypes()
    {
        $resultData = $this->getBenchmarkData();
        if (!is_array($this::EXPECTED_TYPES)) {
            foreach ($this::EXPECTED_TYPES as $key => $type) {
                switch ($type) {
                    case IsType::TYPE_ARRAY:
                        static::assertIsArray($resultData[$key]);
                        break;
                    case IsType::TYPE_FLOAT:
                        static::assertIsFloat($resultData[$key]);
                        break;
                    case IsType::TYPE_INT:
                        static::assertIsInt($resultData[$key]);
                        break;
                    case IsType::TYPE_STRING:
                        static::assertIsString($resultData[$key]);
                        break;
                }
            }

            return;
        }
        $this->checkForTypes($resultData, $this::EXPECTED_TYPES);
    }

    /**
     * @param string $dataName
     */
    protected function installDemoData($dataName)
    {
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $basicContent = $this->openDemoDataFile('basic_setup');
        $dbalConnection->exec($basicContent);
        parent::installDemoData($dataName);
    }

    /**
     * @return array
     */
    protected function getBenchmarkData()
    {
        return $this->getProvider()->getBenchmarkData(Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext(1));
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
                switch ($expectedTypes[$resultKey]) {
                    case IsType::TYPE_ARRAY:
                        static::assertIsArray($resultItem);
                        break;
                    case IsType::TYPE_FLOAT:
                        static::assertIsFloat($resultItem);
                        break;
                    case IsType::TYPE_INT:
                        static::assertIsInt($resultItem);
                        break;
                    case IsType::TYPE_STRING:
                        static::assertIsString($resultItem);
                        break;
                }
            } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
                // Print custom error message
                static::fail(sprintf('Failed asserting that the value for the key %s is of type %s', $resultKey, $expectedTypes[$resultKey]));
            }
        }
    }

    protected function getAssetsFolder()
    {
        return __DIR__ . '/assets/';
    }

    /**
     * @param int $shopId
     *
     * @return ShopContextInterface
     */
    protected function getShopContextByShopId($shopId)
    {
        return Shopware()->Container()->get('shopware_storefront.context_service')->createShopContext($shopId);
    }

    protected function resetConfig()
    {
        /** @var Connection $dbalConnection */
        $dbalConnection = Shopware()->Container()->get('dbal_connection');
        $dbalConnection->update('s_benchmark_config', ['last_order_id' => '0', 'last_customer_id' => '0', 'last_product_id' => '0'], ['1' => '1']);
    }

    protected function sendStatistics($batchSize = null)
    {
        Shopware()->Models()->clear();
        $response = new \Shopware\Bundle\BenchmarkBundle\Struct\StatisticsResponse(new \DateTime('now', new \DateTimeZone('UTC')), 'foo', false);
        $client = $this->createMock(\Shopware\Bundle\BenchmarkBundle\StatisticsClient::class);
        $client->method('sendStatistics')->willReturn($response);
        $service = new \Shopware\Bundle\BenchmarkBundle\Service\StatisticsService(Shopware()->Container()->get('shopware.benchmark_bundle.collector'), $client, Shopware()->Container()->get('shopware.benchmark_bundle.repository.config'), Shopware()->Container()->get('shopware_storefront.context_service'), Shopware()->Container()->get('dbal_connection'));
        $config = Shopware()->Container()->get('shopware.benchmark_bundle.repository.config')->findOneBy(['shopId' => 1]);
        $service->transmit($config, $config->getBatchSize());
    }
}
