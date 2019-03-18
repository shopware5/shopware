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

namespace Shopware\Recovery\Update;

use Shopware\Recovery\Common\HttpClient\Client;

class StoreApi
{
    /**
     * @var Client
     */
    private $client;

    /*
     * @var string
     */
    private $baseUrl;

    /**
     * @param string $baseUrl
     */
    public function __construct(Client $client, $baseUrl)
    {
        $this->client = $client;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string[] $names
     * @param int      $version
     *
     * @return array
     */
    public function getProductsByNamesAndVersion(array $names, $version)
    {
        if (empty($names)) {
            return [];
        }

        $requestPayload = [
            'criterion' => [
                'version' => [
                    $version,
                ],
                'pluginName' => $names,
            ],
        ];

        return $this->doRequest($requestPayload);
    }

    /**
     * @param string[] $names
     *
     * @return array
     */
    public function getProductsByNames(array $names)
    {
        if (empty($names)) {
            return [];
        }

        $requestPayload = [
            'criterion' => [
                'pluginName' => $names,
            ],
        ];

        return $this->doRequest($requestPayload);
    }

    /**
     * @param array $requestPayload
     *
     * @return array
     */
    private function doRequest($requestPayload)
    {
        $queryParams = [
            'method' => 'call',
            'arg0' => 'GET',
            'arg1' => 'product',
            'arg2' => json_encode($requestPayload),
        ];

        $queryParams = http_build_query($queryParams, null, '&');

        $url = $this->baseUrl . '?' . $queryParams;

        $response = $this->client->post($url);

        $result = simplexml_load_string($response->getBody());
        $result = $result->call;

        if ($result->status == 'failed') {
            throw new \RuntimeException($result->response->message);
        }

        $result = $result->response->_search_result;
        $result = json_decode($result);
        $result = json_decode($result->_products, true);

        return $result;
    }
}
