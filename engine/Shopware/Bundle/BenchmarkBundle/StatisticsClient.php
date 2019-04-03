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

namespace Shopware\Bundle\BenchmarkBundle;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Shopware\Bundle\BenchmarkBundle\Exception\StatisticsHydratingException;
use Shopware\Bundle\BenchmarkBundle\Exception\StatisticsSendingException;
use Shopware\Bundle\BenchmarkBundle\Hydrator\HydratorInterface;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsRequest;
use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\HttpClient\Response;
use Shopware\Models\Benchmark\BenchmarkConfig;

/**
 * Responsible for converting business layer requests into HTTP requests and vice/versa.
 */
class StatisticsClient implements StatisticsClientInterface
{
    /**
     * @var string
     */
    private $statisticsApiEndpoint;

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var HydratorInterface
     */
    private $statisticsResponseHydrator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param string $statisticsApiEndpoint
     */
    public function __construct(
        $statisticsApiEndpoint,
        HttpClientInterface $client,
        HydratorInterface $statisticsResponseHydrator,
        LoggerInterface $logger,
        Connection $connection
    ) {
        $this->statisticsApiEndpoint = $statisticsApiEndpoint;
        $this->client = $client;
        $this->statisticsResponseHydrator = $statisticsResponseHydrator;
        $this->logger = $logger;
        $this->connection = $connection;
    }

    /**
     * @throws StatisticsSendingException
     *
     * @return Struct\StatisticsResponse
     */
    public function sendStatistics(StatisticsRequest $statisticsRequest)
    {
        $headers = [
            'User-Agent' => 'Shopware',
        ];

        try {
            $response = $this->client->post($this->statisticsApiEndpoint, $headers, (string) $statisticsRequest);
        } catch (\Exception $ex) {
            $body = '';

            if ($ex instanceof RequestException) {
                $body = $ex->getBody();

                if ($ex->getCode() === 420) {
                    $this->resetBenchmarkConfig($statisticsRequest->getConfig());
                }
            }

            $this->logger->warning(sprintf('Could not send statistics data to %s', $this->statisticsApiEndpoint), [$ex, $body]);

            throw new StatisticsSendingException('Could not send statistics data', 0, $ex);
        }

        return $this->hydrateStatisticsResponse($response);
    }

    /**
     * @throws StatisticsHydratingException
     *
     * @return Struct\StatisticsResponse
     */
    private function hydrateStatisticsResponse(Response $response)
    {
        $data = json_decode(
            $response->getBody(),
            true
        );

        if (!is_array($data)) {
            throw new StatisticsHydratingException(sprintf('Could not interpret statistics response from %s', $this->statisticsApiEndpoint));
        }

        return $this->statisticsResponseHydrator->hydrate($data);
    }

    private function resetBenchmarkConfig(BenchmarkConfig $config)
    {
        $this->connection->update('s_benchmark_config', [
            'id' => Uuid::uuid4(),
            'last_sent' => '1970-01-01 00:00:00',
            'last_received' => '1970-01-01 00:00:00',
            'last_updated_orders_date' => null,
            'last_order_id' => 0,
            'last_customer_id' => 0,
            'last_product_id' => 0,
            'last_analytics_id' => 0,
            'response_token' => null,
            'cached_template' => null,
            'locked' => null,
        ], [
            'shop_id' => $config->getShopId(),
        ]);
    }
}
