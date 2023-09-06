<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\HttpClient;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;

class GuzzleHttpClient implements HttpClientInterface
{
    private ClientInterface $guzzleClient;

    public function __construct(GuzzleFactory $guzzleFactory, array $guzzleConfig = [])
    {
        $this->guzzleClient = $guzzleFactory->createClient($guzzleConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function get($url = null, $headers = []): Response
    {
        try {
            $response = $this->guzzleClient->get($url, ['headers' => $headers]);
        } catch (Exception $e) {
            $body = '';
            if ($e instanceof GuzzleClientException && $e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
            }

            throw new RequestException($e->getMessage(), $e->getCode(), $e, $body);
        }

        return new Response(
            (string) $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function head($url = null, array $headers = []): Response
    {
        try {
            $response = $this->guzzleClient->head($url, ['headers' => $headers]);
        } catch (Exception $e) {
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response(
            (string) $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete($url = null, array $headers = []): Response
    {
        try {
            $response = $this->guzzleClient->delete($url, ['headers' => $headers]);
        } catch (Exception $e) {
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response(
            (string) $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function put($url = null, array $headers = [], $content = null): Response
    {
        try {
            $response = $this->guzzleClient->put($url, $this->formatOptions($headers, $content));
        } catch (Exception $e) {
            throw new RequestException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response(
            (string) $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function patch($url = null, array $headers = [], $content = null): Response
    {
        try {
            $response = $this->guzzleClient->patch($url, $this->formatOptions($headers, $content));
        } catch (Exception $e) {
            $body = '';

            if (($e instanceof GuzzleClientException) && $e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
            }

            throw new RequestException($e->getMessage(), $e->getCode(), $e, $body);
        }

        return new Response(
            (string) $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function post($url = null, array $headers = [], $content = null): Response
    {
        try {
            $response = $this->guzzleClient->post($url, $this->formatOptions($headers, $content));
        } catch (Exception $e) {
            $body = '';

            if (($e instanceof GuzzleClientException) && $e->hasResponse()) {
                $body = (string) $e->getResponse()->getBody();
            }

            throw new RequestException($e->getMessage(), $e->getCode(), $e, $body);
        }

        return new Response(
            (string) $response->getStatusCode(),
            $response->getHeaders(),
            (string) $response->getBody()
        );
    }

    private function formatOptions(array $headers = [], $content = null): array
    {
        $options = [
            'headers' => $headers,
        ];

        if (\is_array($content)) {
            $options['form_params'] = $content;
        } elseif (\is_string($content)) {
            $options['body'] = $content;
        }

        return $options;
    }
}
