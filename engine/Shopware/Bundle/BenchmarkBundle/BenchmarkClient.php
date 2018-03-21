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
use http\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\Bundle\BenchmarkBundle\Exception\BenchmarkHydratingException;
use Shopware\Bundle\BenchmarkBundle\Exception\BenchmarkSendingException;
use Shopware\Bundle\BenchmarkBundle\Hydrator\BenchmarkResponseHydrator;
use Shopware\Bundle\BenchmarkBundle\Struct\BenchmarkRequest;

/**
 * Wandelt die internen Objekte in GuzzleRequest/Responses um
 */
class BenchmarkClient implements BenchmarkClientInterface
{
    /**
     * @var string
     */
    private $benchmarkApiEndpoint;

    /**
     * @var HttpAsyncClient
     */
    private $client;

    /**
     * @var BenchmarkResponseHydrator
     */
    private $benchmarkResponseHydrator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string                    $benchmarkApiEndpoint
     * @param HttpAsyncClient           $client
     * @param BenchmarkResponseHydrator $benchmarkResponseHydrator
     * @param LoggerInterface           $logger
     */
    public function __construct(
        $benchmarkApiEndpoint,
        HttpAsyncClient $client,
        BenchmarkResponseHydrator $benchmarkResponseHydrator,
        LoggerInterface $logger = null)
    {
        $this->benchmarkApiEndpoint = $benchmarkApiEndpoint;
        $this->client = $client;
        $this->benchmarkResponseHydrator = $benchmarkResponseHydrator;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param BenchmarkRequest $benchmarkRequest
     *
     * @throws \Exception
     */
    public function sendBenchmark(BenchmarkRequest $benchmarkRequest)
    {
        $headers = [];

        $promise = $this->client->sendAsyncRequest(new Request('POST', $this->benchmarkApiEndpoint, $headers, (string) $benchmarkRequest));

        $promise->then(function (ResponseInterface $response) {
            return $this->hydrateBenchmarkResponse($response);
        }, function (Exception $ex) {
            $this->logger->warning(sprintf('Could not send benchmark data to %s', $this->benchmarkApiEndpoint), [$ex]);

            throw new BenchmarkSendingException('Could not send benchmark data', 0, $ex);
        });
    }

    /**
     * @param ResponseInterface $response
     *
     * @throws BenchmarkHydratingException
     *
     * @return Struct\BenchmarkResponse
     */
    private function hydrateBenchmarkResponse(ResponseInterface $response)
    {
        $data = json_decode(
            $response->getBody()->getContents(),
            true
        );

        if (!is_array($data)) {
            throw new BenchmarkHydratingException(sprintf('Could not load benchmark from %s', $this->benchmarkApiEndpoint));
        }

        return $this->benchmarkResponseHydrator->hydrate($data);
    }
}
