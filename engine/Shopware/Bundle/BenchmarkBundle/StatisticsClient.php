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

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\Bundle\BenchmarkBundle\Hydrator\StatisticsResponseHydrator;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsRequest;
use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\Response;

class StatisticsClient implements StatisticsClientInterface
{
    /**
     * @var string
     */
    private $statisticsEndpoint;

    /**
     * @var HttpClientInterface
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
     * @param HttpClientInterface        $client
     * @param StatisticsResponseHydrator $statisticsResponseHydrator
     * @param LoggerInterface|null       $logger
     */
    public function __construct(
        $statisticsEndpoint,
        HttpClientInterface $client,
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
     * @throws StatisticsSendingException
     *
     * @return Struct\StatisticsResponse
     */
    public function fetchStatistics(StatisticsRequest $statisticsRequest)
    {
        $headers = [];

        try {
            $response = $this->client->post($this->statisticsEndpoint, $headers, (string) $statisticsRequest);
        } catch (\Exception $ex) {
            $this->logger->warning(sprintf('Could not send statistics data to %s', $this->statisticsEndpoint), [$ex]);

            throw new StatisticsSendingException('Could not send statistics data', 0, $ex);
        }

        return $this->hydrateStatisticsResponse($response);
    }

    /**
     * @param Response $response
     *
     * @throws StatisticsHydratingException
     *
     * @return Struct\StatisticsResponse
     */
    private function hydrateStatisticsResponse(Response $response)
    {
        if (empty($response->getBody())) {
            throw new StatisticsHydratingException(sprintf('Could not read statistics response: %s', $response->getBody()));
        }

        $data = json_decode($response->getBody(), true);

        if (!$data) {
            throw new StatisticsHydratingException(sprintf('Statistics response coudln\'t be parsed as JSON: %s', $response->getBody()));
        }

        return $this->statisticsResponseHydrator->hydrate($data);
    }
}
