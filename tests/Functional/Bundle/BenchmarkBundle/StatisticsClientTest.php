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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle;

use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Shopware\Bundle\BenchmarkBundle\Exception\StatisticsSendingException;
use Shopware\Bundle\BenchmarkBundle\StatisticsClient;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsRequest;
use Shopware\Components\HttpClient\GuzzleHttpClient;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Models\Benchmark\BenchmarkConfig;

class StatisticsClientTest extends BenchmarkTestCase
{
    public function testResetBenchmarkData()
    {
        $requestException = new RequestException('Fooo', 420);

        $httpClient = $this->getMockBuilder(GuzzleHttpClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpClient
            ->expects(static::once())
            ->method('post')
            ->willThrowException($requestException);

        $client = new StatisticsClient(
            'foo',
            $httpClient,
            Shopware()->Container()->get('shopware.benchmark_bundle.hydrator.statistics_response_hydrator'),
            new NullLogger(),
            Shopware()->Container()->get('dbal_connection')
        );

        $config = new BenchmarkConfig(Uuid::uuid4()->toString());
        $config->setLastProductId(100);
        $config->setShopId(55);

        Shopware()->Models()->persist($config);
        Shopware()->Models()->flush($config);

        try {
            $client->sendStatistics(new StatisticsRequest('foo', $config));
        } catch (StatisticsSendingException $e) {
            $oldConfig = $config;
            $config = Shopware()->Models()->getRepository(BenchmarkConfig::class)->findOneBy(['shopId' => 55]);

            static::assertNotEquals($config->getId(), $oldConfig->getId());
            static::assertEquals(0, $config->getLastProductId());
            static::assertEquals(55, $config->getShopId());
        }
    }
}
