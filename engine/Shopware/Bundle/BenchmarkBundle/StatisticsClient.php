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

use GuzzleHttp\Psr7\Request;
use Http\Client\HttpAsyncClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\Bundle\BenchmarkBundle\Hydrator\StatisticsResponseHydrator;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsRequest;

class StatisticsClient implements StatisticsClientInterface
{
    /**
     * @var string
     */
    private $statisticsEndpoint;

    /**
     * @var HttpAsyncClient
     */
    private $client;

    /**
     * @var StatisticsResponseHydrator
     */
    private $statisticsResponseHydrator;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    /**
     * @param string                     $statisticsEndpoint
     * @param HttpAsyncClient            $client
     * @param StatisticsResponseHydrator $statisticsResponseHydrator
     * @param LoggerInterface|null       $logger
     */
    public function __construct(
        $statisticsEndpoint,
        HttpAsyncClient $client,
        StatisticsResponseHydrator $statisticsResponseHydrator,
        LoggerInterface $logger = null
    ) {
        $this->statisticsEndpoint = (string) $statisticsEndpoint;
        $this->statisticsResponseHydrator = $statisticsResponseHydrator;
        $this->client = $client;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param StatisticsRequest $statisticsRequest
     *
     * @throws \Exception
     */
    public function fetchStatistics(StatisticsRequest $statisticsRequest)
    {
        $headers = [];

        $promise = $this->client->sendAsyncRequest(new Request('POST', $this->statisticsEndpoint, $headers, (string) $statisticsRequest));

        $promise->then(function (ResponseInterface $response) {
            return $this->hydrateStatisticsResponse($response);
        }, function (\Exception $ex) {
            $this->logger->warning(sprintf('Could not send statistics data to %s', $this->statisticsEndpoint), [$ex]);

            throw new StatisticsSendingException('Could not send statistics data', 0, $ex);
        });
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws StatisticsHydratingException
     *
     * @return Struct\StatisticsResponse
     */
    private function hydrateStatisticsResponse(ResponseInterface $response)
    {
        if (empty($response->getBody()->getContents())) {
            throw new StatisticsHydratingException(sprintf('Could not read statistics response: %s', $response->getBody()->getContents()));
        }

        return $this->statisticsResponseHydrator->hydrate(['html' => $response->getBody()->getContents()]);
    }
}
