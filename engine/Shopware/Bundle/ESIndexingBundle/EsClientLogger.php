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

namespace Shopware\Bundle\ESIndexingBundle;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;
use Shopware\Bundle\ESIndexingBundle\Console\EvaluationHelperInterface;

class EsClientLogger extends Client
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EvaluationHelperInterface
     */
    private $evaluation;

    /**
     * EsClient constructor.
     *
     * @param Client          $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, LoggerInterface $logger, EvaluationHelperInterface $evaluation)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->evaluation = $evaluation;
    }

    /**
     * {@inheritdoc}
     */
    public function info($params = [])
    {
        $response = $this->client->info($params);

        try {
            $this->handleResult('info', $response, $params);
        } catch (\Exception $e) {
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function ping($params = [])
    {
        return $this->client->ping($params);
    }

    /**
     * {@inheritdoc}
     */
    public function get($params)
    {
        return $this->client->get($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($params)
    {
        return $this->client->getSource($params);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($params)
    {
        return $this->client->delete($params);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByQuery($params = [])
    {
        return $this->client->deleteByQuery($params);
    }

    /**
     * {@inheritdoc}
     */
    public function count($params = [])
    {
        return $this->client->count($params);
    }

    /**
     * {@inheritdoc}
     */
    public function countPercolate($params = [])
    {
        return $this->client->countPercolate($params);
    }

    /**
     * {@inheritdoc}
     */
    public function percolate($params)
    {
        return $this->client->percolate($params);
    }

    /**
     * {@inheritdoc}
     */
    public function mpercolate($params = [])
    {
        return $this->client->mpercolate($params);
    }

    /**
     * {@inheritdoc}
     */
    public function termvectors($params = [])
    {
        return $this->client->termvectors($params);
    }

    /**
     * {@inheritdoc}
     */
    public function termvector($params = [])
    {
        return $this->client->termvector($params);
    }

    /**
     * {@inheritdoc}
     */
    public function mtermvectors($params = [])
    {
        return $this->client->mtermvectors($params);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($params)
    {
        return $this->client->exists($params);
    }

    /**
     * {@inheritdoc}
     */
    public function mlt($params)
    {
        return $this->client->mlt($params);
    }

    /**
     * {@inheritdoc}
     */
    public function mget($params = [])
    {
        return $this->client->mget($params);
    }

    /**
     * {@inheritdoc}
     */
    public function msearch($params = [])
    {
        return $this->client->msearch($params);
    }

    /**
     * {@inheritdoc}
     */
    public function create($params)
    {
        return $this->client->create($params);
    }

    /**
     * {@inheritdoc}
     */
    public function bulk($params = [])
    {
        $response = $this->client->bulk($params);

        try {
            $this->handleResult('bulk', $response, $params);
        } catch (\Exception $e) {
        }

        $this->evaluation->addResult($response);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function index($params)
    {
        return $this->client->index($params);
    }

    /**
     * {@inheritdoc}
     */
    public function suggest($params = [])
    {
        return $this->client->suggest($params);
    }

    /**
     * {@inheritdoc}
     */
    public function explain($params)
    {
        return $this->client->explain($params);
    }

    /**
     * {@inheritdoc}
     */
    public function search($params = [])
    {
        $response = $this->client->search($params);

        try {
            $this->handleResult('search', $response, $params);
        } catch (\Exception $e) {
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function searchExists($params = [])
    {
        return $this->client->searchExists($params);
    }

    /**
     * {@inheritdoc}
     */
    public function searchShards($params = [])
    {
        return $this->client->searchShards($params);
    }

    /**
     * {@inheritdoc}
     */
    public function searchTemplate($params = [])
    {
        return $this->client->searchTemplate($params);
    }

    /**
     * {@inheritdoc}
     */
    public function scroll($params = [])
    {
        return $this->client->scroll($params);
    }

    /**
     * {@inheritdoc}
     */
    public function clearScroll($params = [])
    {
        return $this->client->clearScroll($params);
    }

    /**
     * {@inheritdoc}
     */
    public function update($params)
    {
        return $this->client->update($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getScript($params)
    {
        return $this->client->getScript($params);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteScript($params)
    {
        return $this->client->deleteScript($params);
    }

    /**
     * {@inheritdoc}
     */
    public function putScript($params)
    {
        return $this->client->putScript($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($params)
    {
        return $this->client->getTemplate($params);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTemplate($params)
    {
        return $this->client->deleteTemplate($params);
    }

    /**
     * {@inheritdoc}
     */
    public function putTemplate($params)
    {
        return $this->client->putTemplate($params);
    }

    /**
     * {@inheritdoc}
     */
    public function fieldStats($params = [])
    {
        return $this->client->fieldStats($params);
    }

    /**
     * {@inheritdoc}
     */
    public function reindex($params = [])
    {
        return $this->client->reindex($params);
    }

    /**
     * {@inheritdoc}
     */
    public function updateByQuery($params = [])
    {
        return $this->client->updateByQuery($params);
    }

    /**
     * {@inheritdoc}
     */
    public function renderSearchTemplate($params = [])
    {
        return $this->client->renderSearchTemplate($params);
    }

    /**
     * {@inheritdoc}
     */
    public function indices()
    {
        return $this->client->indices();
    }

    /**
     * {@inheritdoc}
     */
    public function cluster()
    {
        return $this->client->cluster();
    }

    /**
     * {@inheritdoc}
     */
    public function nodes()
    {
        return $this->client->nodes();
    }

    /**
     * {@inheritdoc}
     */
    public function snapshot()
    {
        return $this->client->snapshot();
    }

    /**
     * {@inheritdoc}
     */
    public function cat()
    {
        return $this->client->cat();
    }

    /**
     * {@inheritdoc}
     */
    public function tasks()
    {
        return $this->client->tasks();
    }

    /**
     * {@inheritdoc}
     */
    public function extractArgument(&$params, $arg)
    {
        return $this->client->extractArgument($params, $arg);
    }

    /**
     * @param string $method
     * @param array  $result
     * @param array  $request
     */
    protected function handleResult($method, array $result = [], array $request = [])
    {
        $message = $method;
        switch ($method) {
            case 'bulk':
            case 'search':
                $message .= ' ' . $request['type'] . ' ' . $request['index'];
                break;
            default:
                break;
        }

        $context = [
            'response' => $result,
            'request' => $request,
        ];

        if (isset($result['errors']) && $result['errors'] === true) {
            $this->logger->error($message, $context);
        } else {
            $this->logger->debug($message, $context);
        }
    }
}
