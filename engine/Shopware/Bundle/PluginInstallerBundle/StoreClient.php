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

namespace Shopware\Bundle\PluginInstallerBundle;

use Shopware\Bundle\PluginInstallerBundle\Exception\AccountException;
use Shopware\Bundle\PluginInstallerBundle\Exception\AuthenticationException;
use Shopware\Bundle\PluginInstallerBundle\Exception\DomainVerificationException;
use Shopware\Bundle\PluginInstallerBundle\Exception\LicenceException;
use Shopware\Bundle\PluginInstallerBundle\Exception\OrderException;
use Shopware\Bundle\PluginInstallerBundle\Exception\SbpServerException;
use Shopware\Bundle\PluginInstallerBundle\Exception\ShopSecretException;
use Shopware\Bundle\PluginInstallerBundle\Exception\StoreException;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\RequestException;

/**
 * @package Shopware\Bundle\PluginInstallerBundle
 */
class StoreClient
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $apiEndPoint;

    /**
     * @var Struct\StructHydrator
     */
    private $structHydrator;

    /**
     * @param HttpClientInterface $httpClient
     * @param string $apiEndPoint
     * @param Struct\StructHydrator $structHydrator
     */
    public function __construct(
        HttpClientInterface $httpClient,
        $apiEndPoint,
        Struct\StructHydrator $structHydrator
    ) {
        $this->httpClient = $httpClient;
        $this->apiEndPoint = $apiEndPoint;
        $this->structHydrator = $structHydrator;
    }

    /**
     * @param string $shopwareId
     * @param string $password
     * @return AccessTokenStruct
     * @throws \Exception
     */
    public function getAccessToken($shopwareId, $password)
    {
        $response = $this->doPostRequest(
            '/accesstokens',
            [
                'shopwareId' => $shopwareId,
                'password'   => $password
            ]
        );

        return $this->structHydrator->hydrateAccessToken($response, $shopwareId);
    }

    /**
     * @param string $resource
     * @param array $params
     * @param array $headers
     * @return array
     * @throws \Exception
     */
    public function doGetRequest($resource, $params = [], $headers = [])
    {
        $response = $this->getRequest(
            $resource,
            $params,
            $headers
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * @param AccessTokenStruct $accessToken
     * @param string $resource
     * @param array $params
     * @param array $headers
     * @return array
     * @throws \Exception
     */
    public function doAuthGetRequest(
        AccessTokenStruct $accessToken,
        $resource,
        $params,
        $headers = []
    ) {
        $response = $this->getRequest(
            $resource,
            $params,
            $headers,
            $accessToken
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * @param $resource
     * @param array $params
     * @param array $headers
     * @return mixed
     * @throws \Exception
     */
    public function doGetRequestRaw($resource, $params = [], $headers = [])
    {
        $response = $this->getRequest(
            $resource,
            $params,
            $headers
        );

        return $response->getBody();
    }

    /**
     * @param AccessTokenStruct $accessToken
     * @param string $resource
     * @param array $params
     * @param array $headers
     * @return array
     * @throws \Exception
     */
    public function doAuthGetRequestRaw(
        AccessTokenStruct $accessToken,
        $resource,
        $params,
        $headers = []
    ) {
        $response = $this->getRequest(
            $resource,
            $params,
            $headers,
            $accessToken
        );
        return $response->getBody();
    }

    /**
     * @param string $resource
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function doPostRequest($resource, $params)
    {
        $response = $this->postRequest(
            $resource,
            $params
        );
        return json_decode($response->getBody(), true);
    }

    /**
     * @param AccessTokenStruct $accessToken
     * @param string $resource
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function doAuthPostRequest(
        AccessTokenStruct $accessToken,
        $resource,
        $params
    ) {
        $response = $this->postRequest(
            $resource,
            $params,
            $accessToken
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * @param AccessTokenStruct $accessToken
     * @param $resource
     * @param $params
     * @return \Shopware\Components\HttpClient\Response
     * @throws \Exception
     */
    public function doAuthPostRequestRaw(
        AccessTokenStruct $accessToken,
        $resource,
        $params
    ) {
        $response = $this->postRequest(
            $resource,
            $params,
            $accessToken
        );

        return $response;
    }

    /**
     * @param $resource
     * @param array $params
     * @param array $headers
     * @param accessTokenStruct|null $token
     * @return \Shopware\Components\HttpClient\Response
     * @throws \Exception
     * @internal param null|string $secret
     */
    private function getRequest($resource, $params, $headers = [], AccessTokenStruct $token = null)
    {
        $url = $this->apiEndPoint . $resource;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params, null, '&');
        }

        $header = [];
        if ($token) {
            $header['X-Shopware-Token'] = $token->getToken();
        }

        if (count($headers) > 0) {
            $header = array_merge($header, $headers);
        }

        try {
            $response = $this->httpClient->get($url, $header);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return $response;
    }

    /**
     * @param $resource
     * @param array $params
     * @param AccessTokenStruct $token
     * @return \Shopware\Components\HttpClient\Response
     * @throws StoreException
     * @throws \Exception
     */
    private function postRequest($resource, $params = [], AccessTokenStruct $token = null)
    {
        $url = $this->apiEndPoint . $resource;

        $header = [];
        if ($token) {
            $header['X-Shopware-Token'] = $token->getToken();
        }
        try {
            $response = $this->httpClient->post(
                $url,
                $header,
                json_encode($params)
            );
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return $response;
    }

    /**
     * Handles an Exception thrown by the HttpClient
     * Parses it to detect and extract details provided
     * by SBP about what happened
     *
     * @param RequestException $requestException
     * @throws \Exception
     * @throws SbpServerException
     * @throws AuthenticationException
     * @throws AccountException
     * @throws OrderException
     * @throws LicenceException
     * @throws StoreException
     * @throws DomainVerificationException
     */
    private function handleRequestException(RequestException $requestException)
    {
        if (!$requestException->getBody()) {
            throw $requestException;
        }

        $data = json_decode($requestException->getBody(), true);

        if (!isset($data['code'])) {
            throw $requestException;
        }

        $httpCode = $data['error'];
        $sbpCode  = $data['code'];

        switch ($sbpCode) {

            case 'BinariesException-0':       //Link not found
            case 'BinariesException-1':       //Deserialization failure
            case 'BinariesException-2':       //Upload file is invalid
            case 'BinariesException-3':       //Binary is invalid
            case 'BinariesException-4':       //Binary changeset is invalid
            case 'BinariesException-5':       //Cannot delete binary that succeeded code review
            case 'BinariesException-6':       //Could not load from path
            case 'BinariesException-7':       //Binary is getting checked although not waiting for code review
            case 'BinariesException-8':       //Failed storing encrypted binary
            case 'BinariesException-9':       //Ioncube encryption failed
            case 'PluginLicensesException-6': //Deserialization failed.
            case 'OrdersException-2':         //Deserialization failed
            case 'UsersException-5':          //Deserialization failed
            case 'UserShopsException-8':      //Could not find software version.
                throw new SbpServerException($sbpCode, 'server_error', $httpCode, $requestException);

            case 'BinariesException-10': //Shopware version not given
            case 'BinariesException-12': //Shopware version is invalid
                throw new SbpServerException($sbpCode, 'shopware_version', $httpCode, $requestException);

            case 'BinariesException-11':      //no fitting binary found
                throw new LicenceException($sbpCode, 'no_fitting_binary', $httpCode, $requestException);
            case 'BinariesException-13':
                throw new SbpServerException($sbpCode, 'plugin_name_not_found', $httpCode, $requestException);
            case 'BinariesException-14':
                throw new AuthenticationException($sbpCode, 'token_invalid', $httpCode, $requestException);
            case 'BinariesException-15':
                throw new SbpServerException($sbpCode, 'plugin_licence_not_found', $httpCode, $requestException);
            case 'BinariesException-16':
                throw new SbpServerException($sbpCode, 'wrong_major_version_licence', $httpCode, $requestException);
            case 'BinariesException-17':
                throw new SbpServerException($sbpCode, 'licence_outdated', $httpCode, $requestException);
            case 'BinariesException-18':
                throw new SbpServerException($sbpCode, 'defect_subscription', $httpCode, $requestException);
            case 'BinariesException-19':
                throw new SbpServerException($sbpCode, 'no_version_for_subscription', $httpCode, $requestException);
            case 'BinariesException-20':
                throw new SbpServerException($sbpCode, 'no_version_for_provided_shopware_version', $httpCode, $requestException);
            case 'BinariesException-21':
                throw new SbpServerException($sbpCode, 'no_version_for_provided_shopware_version', $httpCode, $requestException);

            case 'UsersException-4':          //Unauthorized
            case 'OrdersException-0':         //Order authentication failed
            case 'PluginLicensesException-8': //Unauthorized
            case 'UserTokensException-0':     //Authorization failed!
            case 'UserTokensException-1':     //Token invalid.
            case 'UserTokensException-2':     //Given token is invalid.
            case 'LicenseUpgradesException-0': //Given token is invalid.
            case 'LdapTokensException-0':     //Authorization failed.
                throw new AuthenticationException($sbpCode, 'authentication', $httpCode, $requestException);

            case 'UsersException-1':      //Invalid parameters for registration.
                throw new AccountException($sbpCode, 'registration', $httpCode, $requestException);

            case 'UsersException-2':      //ShopwareID is already taken
                throw new AccountException($sbpCode, 'shopware_id_already_taken', $httpCode, $requestException);

            case 'UsersException-3':      //User is invalid
                throw new AccountException($sbpCode, 'invalid_user', $httpCode, $requestException);

            case 'UsersException-6':      //Invalid password reset parameters
                throw new AccountException($sbpCode, 'invalid_password_reset', $httpCode, $requestException);

            case 'UserTokensException-3': //Account is banned.
                throw new AccountException($sbpCode, 'account_banned', $httpCode, $requestException);

            case 'OrdersException-1':         //Ordered plugin not found
            case 'PluginLicensesException-1': //Referenced plugin not found.
                throw new OrderException($sbpCode, 'plugin_not_found', $httpCode, $requestException);

            case 'OrdersException-4':         //Insufficient balance
                throw new OrderException($sbpCode, 'insufficient_balance', $httpCode, $requestException);

            case 'OrdersException-3':  //Empty order
            case 'OrdersException-5':  //Order invalid
            case 'OrdersException-6':  //Order position invalid
                throw new OrderException($sbpCode, 'order_invalid', $httpCode, $requestException);

            case 'OrdersException-7':  //Shop version incompatible with license version
                throw new OrderException($sbpCode, 'incompatible_version', $httpCode, $requestException);

            case 'PluginLicensesException-0': //License not found.
                throw new LicenceException($sbpCode, 'licence_not_found', $httpCode, $requestException);

            case 'PluginLicensesException-2': //License is invalid.
            case 'PluginLicensesException-9': //Invalid parameters.
                throw new LicenceException($sbpCode, 'licence_invalid', $httpCode, $requestException);

            case 'PluginLicensesException-3': //Referenced shop not found.
                throw new LicenceException($sbpCode, 'shop_not_found', $httpCode, $requestException);

            case 'PluginLicensesException-4': //License is already ordered for this shop.
            case 'PluginLicensesException-7': //License already ordered with a better price model.
                throw new LicenceException($sbpCode, 'already_ordered', $httpCode, $requestException);

            case 'DomainVerificationException-0':   //Invalid domain.
                throw new DomainVerificationException($sbpCode, 'invalid_domain', $httpCode, $requestException);
            case 'DomainVerificationException-1':   //Unauthorized.
                throw new DomainVerificationException($sbpCode, 'unauthorized', $httpCode, $requestException);
            case 'DomainVerificationException-2':   //Verification failed.
                throw new DomainVerificationException($sbpCode, 'verification_failed', $httpCode, $requestException);
            case 'DomainVerificationException-3':   //Unknown Shopware ID.
                throw new DomainVerificationException($sbpCode, 'unknown_id', $httpCode, $requestException);
            case 'DomainVerificationException-4':   //Domain already in use.
                throw new DomainVerificationException($sbpCode, 'domain_in_use', $httpCode, $requestException);
            case 'ShopSecretsException-2':
                throw new ShopSecretException($sbpCode, 'shop_secret_invalid', $httpCode, $requestException);
        }

        throw new StoreException(
            $sbpCode,
            $data['reason'],
            $httpCode,
            $requestException
        );
    }
}
