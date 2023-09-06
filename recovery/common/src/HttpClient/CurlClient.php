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

namespace Shopware\Recovery\Common\HttpClient;

class CurlClient implements Client
{
    public const METHOD_GET = 'GET';
    public const METHOD_PUT = 'PUT';
    public const METHOD_POST = 'POST';
    public const METHOD_DELETE = 'DELETE';

    /**
     * @var string[]
     */
    protected $validMethods = [
        self::METHOD_GET,
        self::METHOD_PUT,
        self::METHOD_POST,
        self::METHOD_DELETE,
    ];

    /**
     * {@inheritdoc}
     */
    public function get($url, array $header = [])
    {
        return $this->call($url, self::METHOD_GET, $header);
    }

    /**
     * {@inheritdoc}
     */
    public function post($url, $data = null, array $header = [])
    {
        return $this->call($url, self::METHOD_POST, $header, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function put($url, $data = null, array $header = [])
    {
        return $this->call($url, self::METHOD_PUT, $header, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($url, $data = null, array $header = [])
    {
        return $this->call($url, self::METHOD_DELETE, $header, $data);
    }

    /**
     * @param string       $url
     * @param string       $method
     * @param string[]     $header
     * @param string|array $data
     *
     * @throws ClientException
     *
     * @return Response
     */
    private function call($url, $method = self::METHOD_GET, array $header = [], $data = null)
    {
        if (!\in_array($method, $this->validMethods)) {
            throw new ClientException('Invalid HTTP-METHOD: ' . $method);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ClientException('Invalid URL given');
        }

        // Initializes the cURL instance
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $content = curl_exec($curl);

        $error = curl_errno($curl);
        $errmsg = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        if ($content === false) {
            throw new ClientException($errmsg, $error);
        }

        list($header, $body) = explode("\r\n\r\n", $content, 2);

        return new Response($body, $httpCode, $header);
    }
}
