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

namespace Shopware\Components\HttpClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class GuzzleHttpClient implements HttpClientInterface
{
    /**
     * @var ClientInterface
     */
    private $guzzleClient;

    /**
     * @param GuzzleFactory $guzzleFactory
     */
    public function __construct(GuzzleFactory $guzzleFactory)
    {
        $this->guzzleClient = $guzzleFactory->createClient();
    }

    /**
     * {@inheritdoc}
     */
    public function get($url = null, $headers = [])
    {
        try {
            $response = $this->guzzleClient->get($url, ['headers' => $headers]);
        } catch (\Exception $e) {
            /** @var $e GuzzleClientException */
            $body = '';
            if ($e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
            }

            throw new RequestException(
                $e->getMessage(),
                $e->getCode(),
                $e,
                $body
            );
        }

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function head($url = null, array $headers = [])
    {
        try {
            $response = $this->guzzleClient->head($url, ['headers' => $headers]);
        } catch (\Exception $e) {
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete($url = null, array $headers = [])
    {
        try {
            $response = $this->guzzleClient->delete($url, ['headers' => $headers]);
        } catch (\Exception $e) {
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function put($url = null, array $headers = [], $content = null)
    {
        try {
            // http://guzzle.readthedocs.org/en/latest/clients.html#request-options
            $options = [
                'headers' => $headers,
                'body' => $content,
            ];

            $response = $this->guzzleClient->put($url, $options);
        } catch (\Exception $e) {
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function patch($url = null, array $headers = [], $content = null)
    {
        try {
            // http://guzzle.readthedocs.org/en/latest/clients.html#request-options
            $options = [
                'headers' => $headers,
                'body' => $content,
            ];

            $response = $this->guzzleClient->patch($url, $options);
        } catch (\Exception $e) {
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function post($url = null, array $headers = [], $content = null)
    {
        try {
            // http://guzzle.readthedocs.org/en/latest/clients.html#request-options
            $options = [
                'headers' => $headers,
                'body' => $content,
            ];

            $response = $this->guzzleClient->post($url, $options);
        } catch (\Exception $e) {
            /** @var $e GuzzleClientException */
            $body = '';
            if ($e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
            }

            throw new RequestException(
                $e->getMessage(),
                $e->getCode(),
                $e,
                $body
            );
        }

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }
}
