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
use Shopware\Bundle\BenchmarkBundle\Exception\StatisticsHydratingException;
use Shopware\Bundle\BenchmarkBundle\Exception\StatisticsSendingException;
use Shopware\Bundle\BenchmarkBundle\Hydrator\StatisticsResponseHydrator;
use Shopware\Bundle\BenchmarkBundle\Struct\StatisticsRequest;
use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\Response;

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
     * @var StatisticsResponseHydrator
     */
    private $statisticsResponseHydrator;

    /**
     * @var BenchmarkEncryption
     */
    private $benchmarkEncryption;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $encryptionMethod = 'aes256';

    /**
     * @param string                     $statisticsApiEndpoint
     * @param HttpClientInterface        $client
     * @param StatisticsResponseHydrator $statisticsResponseHydrator
     * @param BenchmarkEncryption        $benchmarkEncryption
     * @param LoggerInterface            $logger
     */
    public function __construct(
        $statisticsApiEndpoint,
        HttpClientInterface $client,
        StatisticsResponseHydrator $statisticsResponseHydrator,
        BenchmarkEncryption $benchmarkEncryption,
        LoggerInterface $logger = null)
    {
        $this->statisticsApiEndpoint = $statisticsApiEndpoint;
        $this->client = $client;
        $this->statisticsResponseHydrator = $statisticsResponseHydrator;
        $this->benchmarkEncryption = $benchmarkEncryption;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param StatisticsRequest $statisticsRequest
     *
     * @throws StatisticsSendingException
     *
     * @return Struct\StatisticsResponse
     */
    public function sendStatistics(StatisticsRequest $statisticsRequest)
    {
        $headers = [
            'User-Agent' => 'Shopware',
        ];

        /* Deactivating encryption for the moment
        if ($this->benchmarkEncryption->isEncryptionSupported($this->encryptionMethod)) {
            $payload = json_encode(['data' => $this->benchmarkEncryption->encryptData((string) $statisticsRequest, $this->encryptionMethod)]);
        }*/

        try {
            $response = $this->client->post($this->statisticsApiEndpoint, $headers, (string) $statisticsRequest);
        } catch (\Exception $ex) {
            $this->logger->warning(sprintf('Could not send statistics data to %s', $this->statisticsApiEndpoint), [$ex]);

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
        $data = json_decode(
            $response->getBody(),
            true
        );

        if (!is_array($data)) {
            throw new StatisticsHydratingException(sprintf('Could not interpret statistics response from %s', $this->statisticsApiEndpoint));
        }

        return $this->statisticsResponseHydrator->hydrate($data);
    }
}
