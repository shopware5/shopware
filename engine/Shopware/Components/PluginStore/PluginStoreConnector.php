<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Components\PluginStore;

use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\RequestException;

/**
 * class PluginStoreConnector
 *
 * @category  Shopware
 * @package   Shopware\Components\PluginStore\PluginStoreConnectionInterface
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PluginStoreConnector
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    private $apiEndpoint;

    /**
     * @param HttpClientInterface $httpClient
     * @param $apiEndpoint
     */
    public function __construct(HttpClientInterface $httpClient, $apiEndpoint)
    {
        $this->httpClient = $httpClient;
        $this->apiEndpoint = $apiEndpoint;
    }

    /**
     * Requests the creation of a new Shopware ID anc account (registration action)
     */
    public function ping()
    {
        $urlParts = parse_url($this->apiEndpoint);

        $connected = fsockopen($urlParts['host'], $urlParts['port']);

        if ($connected){
            $isConn = true;
            fclose($connected);
        }else{
            $isConn = false;
        }
        return $isConn;
    }

    /**
     * Requests the creation of a new Shopware ID anc account (registration action)
     *
     * @param $shopwareId
     * @param $email
     * @param $password
     * @param $localeId
     * @return \Shopware\Components\HttpClient\Response
     * @throws \RuntimeException
     */
    public function register($shopwareId, $email, $password, $localeId)
    {
        $url = $this->apiEndpoint . 'users';

        $postData = array(
            'shopwareId' => $shopwareId,
            'email'      => $email,
            'password'   => $password,
            'localeId'   => $localeId
        );

        try {
            $response = $this->httpClient->post($url, [], json_encode($postData));
        } catch (RequestException $e) {
            $responseBody = $e->getBody();
            if (!empty($responseBody)) {
                $jsonResponseBody = json_decode($responseBody);
                if (!empty($jsonResponseBody)) {
                    throw new \RuntimeException('Could not create user - '. $jsonResponseBody->reason);
                }
            }
            throw new \RuntimeException('Could not create user');
        }

        if ($response->getStatusCode() != 201) {
            $result = json_decode($response->getBody());
            throw new \RuntimeException('Could not create user - '. $result->reason);
        }

        return $response;
    }

    /**
     * Gets a locale list
     *
     * @return mixed
     */
    public function getLocales()
    {
        $url = $this->apiEndpoint . "locales";

        try {
            $response = $this->httpClient->get($url);
        } catch (RequestException $e) {
            $responseBody = $e->getBody();
            if (!empty($responseBody)) {
                $jsonResponseBody = json_decode($responseBody);
                if (!empty($jsonResponseBody)) {
                    throw new \RuntimeException('Could not get locales - '. $jsonResponseBody->reason);
                }
            }
            throw new \RuntimeException('Could not locales');
        }

        $data = json_decode($response->getBody());
        return $data;
    }

    /**
     * Get the list of shops (and details) associated to the given user
     *
     * @param $token
     * @param $shopwareId
     * @return mixed
     */
    public function getShops($token, $shopwareId)
    {
        $url = $this->apiEndpoint . "shops?shopwareId=".$shopwareId;

        $header = ['X-Shopware-Token' => $token];

        try {
            $response = $this->httpClient->get($url, $header);
        } catch (RequestException $e) {
            $responseBody = $e->getBody();
            if (!empty($responseBody)) {
                $jsonResponseBody = json_decode($responseBody);
                if (!empty($jsonResponseBody)) {
                    throw new \RuntimeException('Could not get shops - '. $jsonResponseBody->reason);
                }
            }
            throw new \RuntimeException('Could not shops');
        }

        $data = json_decode($response->getBody());
        return $data;
    }

    /**
     * Requests the domain hash and filename needed to generate the
     * validation key, so that the current domain can be validated
     *
     * @param $domain
     * @param $token
     * @return \Shopware\Components\HttpClient\Response
     */
    public function getDomainHash($domain, $token)
    {
        $url = $this->apiEndpoint . "domainhashes";

        $header = ['X-Shopware-Token' => $token];

        $postData = array(
            'domain' => $domain,
        );

        try {
            $response = $this->httpClient->post($url, $header, json_encode($postData));
        } catch (RequestException $e) {
            $responseBody = $e->getBody();
            if (!empty($responseBody)) {
                $jsonResponseBody = json_decode($responseBody);
                if (!empty($jsonResponseBody)) {
                    throw new \RuntimeException('Could not register domain - '. $jsonResponseBody->reason);
                }
            }
            throw new \RuntimeException('Could not register domain');
        }

        return json_decode($response->getBody());
    }

    /**
     * Requests the validation of the current installation's domain
     *
     * @param $domain
     * @param $shopwareId
     * @param $token
     * @return mixed
     */
    public function verifyDomain($domain, $shopwareId, $token)
    {
        $url = $this->apiEndpoint . "domainverifications";

        $header = ['X-Shopware-Token' => $token];

        $postData = array(
            'shopwareId' => $shopwareId,
            'domain' => $domain
        );

        try {
            $response = $this->httpClient->post($url, $header, json_encode($postData));
        } catch (RequestException $e) {
            $responseBody = $e->getBody();
            if (!empty($responseBody)) {
                $jsonResponseBody = json_decode($responseBody);
                if (!empty($jsonResponseBody)) {
                    throw new \RuntimeException('Could not verify domain -  '. $jsonResponseBody->reason);
                }
            }
            throw new \RuntimeException('Could not verify domain');
        }

        return json_decode($response->getBody());
    }

    /**
     * Gets the token. If the current token is invalid, empty or will expire soon,
     * gets a new one from the server using the provided auth credentials
     *
     * @param $shopwareId
     * @param $password
     * @return token
     */
    public function getToken($shopwareId = null, $password = null)
    {
        $tokenData = Shopware()->BackendSession()->accessToken;

        if (empty($tokenData) || strtotime($tokenData->expire->date) >= strtotime("+30 seconds")) {
            if (empty($shopwareId) || empty($password)) {
                throw new \RuntimeException('Could not login - missing login data');
            }

            $url = $this->apiEndpoint . "accesstokens";

            $postData = array(
                'shopwareId' => $shopwareId,
                'password'   => $password,
            );

            try {
                $response = $this->httpClient->post($url, [], json_encode($postData));
            } catch (RequestException $e) {
                $responseBody = $e->getBody();
                if (!empty($responseBody)) {
                    $jsonResponseBody = json_decode($responseBody);
                    if (!empty($jsonResponseBody)) {
                        throw new \RuntimeException('Could not login - '. $jsonResponseBody->reason);
                    }
                }
                throw new \RuntimeException('Could not login');
            }

            $tokenData = json_decode($response->getBody());

            Shopware()->BackendSession()->accessToken = $tokenData;
        }

        return $tokenData->token;
    }
}