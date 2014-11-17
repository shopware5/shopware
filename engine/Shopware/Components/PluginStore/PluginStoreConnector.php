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

namespace Shopware\Components\PluginStore;

use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\RequestException;

/**
 * @category  Shopware
 * @package   Shopware\Components\PluginStore
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PluginStoreConnector
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string API endpoint address
     */
    private $apiEndpoint;

    /**
     * @param HttpClientInterface $httpClient
     * @param string[] $apiEndpointConfig
     */
    public function __construct(HttpClientInterface $httpClient, $apiEndpointConfig)
    {
        $this->httpClient = $httpClient;
        $this->apiEndpoint = $apiEndpointConfig['apiEndpoint'];
    }

    /**
     * Pings SBP to see if a connection is available and the service is up
     *
     * @return boolean
     */
    public function ping()
    {
        $url = $this->apiEndpoint . 'ping';

        try {
            $response = $this->httpClient->get($url);
        } catch (RequestException $e) {
            $responseBody = $e->getBody();
            if (!empty($responseBody)) {
                $jsonResponseBody = json_decode($responseBody);
                if (!empty($jsonResponseBody)) {
                    throw new \RuntimeException('Could not ping server - '. $jsonResponseBody->reason);
                }
            }
            throw new \RuntimeException('Could not ping server');
        }

        $data = json_decode($response->getBody()) ? : false;
        return $data;
    }

    /**
     * Requests the creation of a new Shopware ID anc account (registration action)
     *
     * @param string $shopwareId
     * @param string $email
     * @param string $password
     * @param int $localeId
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
     * @return Obj[] array of locale details
     * @throws \RuntimeException
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
     * @param string $token
     * @param string $shopwareId
     * @return Obj[] array of shop details
     * @throws \RuntimeException
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
     * @param string $domain
     * @param string $token
     * @return array Filename and domain hash of the domain validation file
     * @throws \RuntimeException
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
     * @param string $domain
     * @param string $shopwareId
     * @param string $token
     * @return array Result of the validation operation (empty if successful)
     * @throws \RuntimeException
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
     * Gets an access token from the server using the provided auth credentials
     *
     * @param string $shopwareId
     * @param string $password
     * @return array Token to access the API
     * @throws \RuntimeException
     */
    public function getToken($shopwareId = null, $password = null)
    {
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

        return json_decode($response->getBody());
    }
}
