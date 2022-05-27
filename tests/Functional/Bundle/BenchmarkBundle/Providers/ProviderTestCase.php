<?php

declare(strict_types=1);
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

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\ExpectationFailedException;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\BenchmarkBundle\Service\StatisticsService;
use Shopware\Bundle\BenchmarkBundle\StatisticsClient;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsResponse;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models\Benchmark\BenchmarkConfig;
use Shopware\Tests\Functional\Bundle\BenchmarkBundle\BenchmarkTestCase;

abstract class ProviderTestCase extends BenchmarkTestCase
{
    protected const SERVICE_ID = '';
    protected const EXPECTED_KEYS_COUNT = 0;
    protected const EXPECTED_TYPES = [];

    private ?BenchmarkProviderInterface $provider = null;

    /**
     * @group BenchmarkBundle
     */
    public function testGetArrayKeysFit(): void
    {
        $resultData = $this->getBenchmarkData();
        $arrayKeys = array_keys($resultData);
        static::assertCount(static::EXPECTED_KEYS_COUNT, $arrayKeys);
    }

    /**
     * @group BenchmarkBundle
     */
    public function testGetValidateTypes(): void
    {
        $resultData = $this->getBenchmarkData();
        foreach (static::EXPECTED_TYPES as $key => $type) {
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

        $this->checkForTypes($resultData, static::EXPECTED_TYPES);
    }

    protected function installDemoData(string $dataName): void
    {
        $dbalConnection = $this->getContainer()->get(Connection::class);
        $basicContent = $this->openDemoDataFile('basic_setup');
        $dbalConnection->executeStatement($basicContent);
        parent::installDemoData($dataName);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getBenchmarkData(): array
    {
        return $this->getProvider()->getBenchmarkData($this->getContainer()->get(ContextServiceInterface::class)->createShopContext(1));
    }

    protected function getProvider(): BenchmarkProviderInterface
    {
        if ($this->provider === null) {
            $provider = $this->getContainer()->get(static::SERVICE_ID);
            static::assertInstanceOf(BenchmarkProviderInterface::class, $provider);
            $this->provider = $provider;
        }

        return $this->provider;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $expectedTypes
     */
    protected function checkForTypes(array $data, array $expectedTypes): void
    {
        foreach ($data as $resultKey => $resultItem) {
            if (!$expectedTypes[$resultKey]) {
                continue;
            }
            if (\is_array($expectedTypes[$resultKey])) {
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
            } catch (ExpectationFailedException $e) {
                // Print custom error message
                static::fail(sprintf('Failed asserting that the value for the key %s is of type %s', $resultKey, $expectedTypes[$resultKey]));
            }
        }
    }

    protected function getAssetsFolder(): string
    {
        return __DIR__ . '/assets/';
    }

    protected function getShopContextByShopId(int $shopId): ShopContextInterface
    {
        return $this->getContainer()->get(ContextServiceInterface::class)->createShopContext($shopId);
    }

    protected function resetConfig(): void
    {
        $dbalConnection = $this->getContainer()->get('dbal_connection');
        $dbalConnection->update(
            's_benchmark_config',
            [
                'last_order_id' => '0',
                'last_customer_id' => '0',
                'last_product_id' => '0',
            ],
            ['NULL' => null]
        );
    }

    protected function sendStatistics(): void
    {
        $this->getContainer()->get('models')->clear();
        $response = new StatisticsResponse(new DateTime('now', new DateTimeZone('UTC')), 'foo', false);
        $client = $this->createMock(StatisticsClient::class);
        $client->method('sendStatistics')->willReturn($response);

        $service = new StatisticsService(
            $this->getContainer()->get('shopware.benchmark_bundle.collector'),
            $client,
            $this->getContainer()->get('shopware.benchmark_bundle.repository.config'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('dbal_connection')
        );

        $config = $this->getContainer()->get('shopware.benchmark_bundle.repository.config')->findOneBy(['shopId' => 1]);
        static::assertInstanceOf(BenchmarkConfig::class, $config);
        $service->transmit($config, $config->getBatchSize());
    }
}
