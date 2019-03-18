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

class EsClientLogger extends Client implements EsClientInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EvaluationHelperInterface
     */
    private $evaluation;

    public function __call($name, $arguments)
    {
        parent::$name(...$arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setEvaluation(EvaluationHelperInterface $evaluation)
    {
        $this->evaluation = $evaluation;
    }

    /**
     * {@inheritdoc}
     */
    public function info($params = [])
    {
        $response = parent::info($params);

        try {
            $this->handleResult('info', $response, $params);
        } catch (\Exception $e) {
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function bulk($params = [])
    {
        $response = parent::bulk($params);

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
    public function search($params = [])
    {
        $response = parent::search($params);

        try {
            $this->handleResult('search', $response, $params);
        } catch (\Exception $e) {
        }

        return $response;
    }

    /**
     * @param string $method
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
