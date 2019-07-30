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
use Shopware\Bundle\PluginInstallerBundle\Exception\MissingDoubleOptInConfirmationException;
use Shopware\Bundle\PluginInstallerBundle\Exception\MissingMasterDataException;
use Shopware\Bundle\PluginInstallerBundle\Exception\OrderException;
use Shopware\Bundle\PluginInstallerBundle\Exception\SbpServerException;
use Shopware\Bundle\PluginInstallerBundle\Exception\ShopNotFoundException;
use Shopware\Bundle\PluginInstallerBundle\Exception\ShopSecretException;
use Shopware\Bundle\PluginInstallerBundle\Exception\StoreException;
use Shopware\Bundle\PluginInstallerBundle\Service\UniqueIdGeneratorInterface;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Components\HttpClient\HttpClientInterface;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\HttpClient\Response;
use Shopware\Components\OpenSSLVerifier;

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
     * @var OpenSSLVerifier
     */
    private $openSSLVerifier;

    /**
     * @var UniqueIdGeneratorInterface
     */
    private $uniqueIdGenerator;

    /**
     * @param string $apiEndPoint
     */
    public function __construct(
        HttpClientInterface $httpClient,
        $apiEndPoint,
        Struct\StructHydrator $structHydrator,
        OpenSSLVerifier $openSSLVerifier,
        UniqueIdGeneratorInterface $uniqueIdGenerator
    ) {
        $this->httpClient = $httpClient;
        $this->apiEndPoint = $apiEndPoint;
        $this->structHydrator = $structHydrator;
        $this->openSSLVerifier = $openSSLVerifier;
        $this->uniqueIdGenerator = $uniqueIdGenerator;
    }

    /**
     * @param string $shopwareId
     * @param string $password
     *
     * @throws \Exception
     *
     * @return AccessTokenStruct
     */
    public function getAccessToken($shopwareId, $password)
    {
        $response = $this->doPostRequest(
            '/accesstokens',
            [
                'shopwareId' => $shopwareId,
                'password' => $password,
            ]
        );

        return $this->structHydrator->hydrateAccessToken($response, $shopwareId);
    }

    /**
     * @param string $resource
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Exception
     *
     * @return array
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
     * @param string $resource
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Exception
     *
     * @return array
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
     * @param string $resource
     * @param array  $params
     * @param array  $headers
     *
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
     * @param string $resource
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Exception
     *
     * @return string
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
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Exception
     *
     * @return array
     */
    public function doPostRequest($resource, $params, $headers = [])
    {
        $response = $this->postRequest(
            $resource,
            $params,
            $headers
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $resource
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return array
     */
    public function doAuthPostRequest(
        AccessTokenStruct $accessToken,
        $resource,
        $params
    ) {
        $response = $this->postRequest(
            $resource,
            $params,
            [],
            $accessToken
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * @param string $resource
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function doAuthPostRequestRaw(
        AccessTokenStruct $accessToken,
        $resource,
        $params
    ) {
        $response = $this->postRequest(
            $resource,
            $params,
            [],
            $accessToken
        );

        return $response;
    }

    /**
     * @return bool
     */
    public function doPing()
    {
        $response = $this->httpClient->get($this->apiEndPoint . '/ping');
        $this->verifyResponseSignature($response);

        return json_decode($response->getBody(), true) ?: false;
    }

    /**
     * @param string $eventName
     * @param array  $additionalData
     *
     * @return array|false
     */
    public function doTrackEvent($eventName, $additionalData = [])
    {
        $payload = [
            'additionalData' => $additionalData,
            'instanceId' => $this->uniqueIdGenerator->getUniqueId(),
            'event' => $eventName,
        ];

        try {
            $response = $this->httpClient->post($this->apiEndPoint . '/tracking/events', [], json_encode($payload));
            $this->verifyResponseSignature($response);
        } catch (RequestException $ex) {
            return false;
        }

        return json_decode($response->getBody(), true) ?: false;
    }

    /**
     * @param string $resource
     * @param array  $params
     * @param array  $headers
     *
     * @throws \Exception
     *
     * @return Response
     *
     * @internal param null|string $secret
     */
    private function getRequest($resource, $params, $headers = [], AccessTokenStruct $token = null)
    {
        $url = $this->apiEndPoint . $resource;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params, '', '&');
        }
        $header = [];
        if ($token) {
            $header['X-Shopware-Token'] = $token->getToken();
        }

        if (count($headers) > 0) {
            $header = array_merge($header, $headers);
        }

        $response = null;

        try {
            $response = $this->httpClient->get($url, $header);
            $this->verifyResponseSignature($response);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return $response;
    }

    /**
     * @param string            $resource
     * @param array             $params
     * @param array             $headers
     * @param AccessTokenStruct $token
     *
     * @throws StoreException
     * @throws \Exception
     *
     * @return Response
     */
    private function postRequest($resource, $params = [], $headers = [], AccessTokenStruct $token = null)
    {
        $url = $this->apiEndPoint . $resource;

        $header = [];
        if ($token) {
            $header['X-Shopware-Token'] = $token->getToken();
        }

        if (count($headers) > 0) {
            $header = array_merge($header, $headers);
        }

        $response = null;
        try {
            $response = $this->httpClient->post(
                $url,
                $header,
                json_encode($params)
            );
            $this->verifyResponseSignature($response);
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

        $httpCode = array_key_exists('error', $data) ? $data['error'] : 0;
        $sbpCode = $data['code'];

        switch ($sbpCode) {
            case 'BinariesException-0':       // Link not found
            case 'BinariesException-1':       // Deserialization failure
            case 'BinariesException-2':       // Upload file is invalid
            case 'BinariesException-3':       // Binary is invalid
            case 'BinariesException-4':       // Binary changeset is invalid
            case 'BinariesException-5':       // Cannot delete binary that succeeded code review
            case 'BinariesException-6':       // Could not load from path
            case 'BinariesException-7':       // Binary is getting checked although not waiting for code review
            case 'BinariesException-8':       // Failed storing encrypted binary
            case 'BinariesException-9':       // Ioncube encryption failed
            case 'PluginLicensesException-6': // Deserialization failed.
            case 'OrdersException-2':         // Deserialization failed
            case 'UsersException-5':          // Deserialization failed
            case 'UserShopsException-8':      // Could not find software version.
                throw new SbpServerException($sbpCode, 'server_error', $httpCode, $requestException);
            case 'BinariesException-10': // Shopware version not given
            case 'BinariesException-12': // Shopware version is invalid
                throw new SbpServerException($sbpCode, 'shopware_version', $httpCode, $requestException);
            case 'BinariesException-11':      // No fitting binary found
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
            case 'UsersException-4':          // Unauthorized
            case 'OrdersException-0':         // Order authentication failed
            case 'PluginLicensesException-8': // Unauthorized
            case 'UserTokensException-0':     // Authorization failed!
            case 'UserTokensException-1':     // Token invalid.
            case 'UserTokensException-2':     // Given token is invalid.
            case 'LicenseUpgradesException-0': // Given token is invalid.
            case 'LdapTokensException-0':     // Authorization failed.
                throw new AuthenticationException($sbpCode, 'authentication', $httpCode, $requestException);
            case 'UsersException-1':      // Invalid parameters for registration.
                throw new AccountException($sbpCode, 'registration', $httpCode, $requestException);
            case 'UsersException-2':      // ShopwareID is already taken
                throw new AccountException($sbpCode, 'shopware_id_already_taken', $httpCode, $requestException);
            case 'UsersException-3':      // User is invalid
                throw new AccountException($sbpCode, 'invalid_user', $httpCode, $requestException);
            case 'UsersException-6':      // Invalid password reset parameters
                throw new AccountException($sbpCode, 'invalid_password_reset', $httpCode, $requestException);
            case 'UserTokensException-3': // Account is banned.
                throw new AccountException($sbpCode, 'account_banned', $httpCode, $requestException);
            case 'OrdersException-1':         // Ordered plugin not found
            case 'PluginLicensesException-1': // Referenced plugin not found.
                throw new OrderException($sbpCode, 'plugin_not_found', $httpCode, $requestException);
            case 'OrdersException-4':         // Insufficient balance
                throw new OrderException($sbpCode, 'insufficient_balance', $httpCode, $requestException);
            case 'OrdersException-3':  // Empty order
            case 'OrdersException-5':  // Order invalid
            case 'OrdersException-6':  // Order position invalid
                throw new OrderException($sbpCode, 'order_invalid', $httpCode, $requestException);
            case 'OrdersException-7':  // Shop version incompatible with license version
                throw new OrderException($sbpCode, 'incompatible_version', $httpCode, $requestException);
            case 'PluginLicensesException-0': // License not found.
                throw new LicenceException($sbpCode, 'licence_not_found', $httpCode, $requestException);
            case 'PluginLicensesException-2': // License is invalid.
            case 'PluginLicensesException-9': // Invalid parameters.
                throw new LicenceException($sbpCode, 'licence_invalid', $httpCode, $requestException);
            case 'PluginLicensesException-3': // Referenced shop not found.
                throw new LicenceException($sbpCode, 'shop_not_found', $httpCode, $requestException);
            case 'PluginLicensesException-4': // License is already ordered for this shop.
            case 'PluginLicensesException-7': // License already ordered with a better price model.
                throw new LicenceException($sbpCode, 'already_ordered', $httpCode, $requestException);
            case 'DomainVerificationException-0':   // Invalid domain.
                throw new DomainVerificationException($sbpCode, 'invalid_domain', $httpCode, $requestException);
            case 'DomainVerificationException-1':   // Unauthorized.
                throw new DomainVerificationException($sbpCode, 'unauthorized', $httpCode, $requestException);
            case 'DomainVerificationException-2':   // Verification failed.
                throw new DomainVerificationException($sbpCode, 'verification_failed', $httpCode, $requestException);
            case 'DomainVerificationException-3':   // Unknown Shopware ID.
                throw new DomainVerificationException($sbpCode, 'unknown_id', $httpCode, $requestException);
            case 'DomainVerificationException-4':   // Domain already in use.
                throw new DomainVerificationException($sbpCode, 'domain_in_use', $httpCode, $requestException);
            case 'ShopSecretsException-2':
                throw new ShopSecretException($sbpCode, 'shop_secret_invalid', $httpCode, $requestException);
            case 'UserShopsException-0':
                throw new ShopNotFoundException($sbpCode, 'shop_not_found', $httpCode, $requestException);
            case 'OrdersException-14':
                throw new MissingMasterDataException($sbpCode, 'missing_master_data', $httpCode, $requestException);
            case 'UsersException-16':
                throw new MissingDoubleOptInConfirmationException($sbpCode, 'missing_doi_confirmation', $httpCode, $requestException);
        }

        $reason = array_key_exists('reason', $data) ? $data['reason'] : sprintf('Unknown error occurred. (%s)', $sbpCode);

        throw new StoreException(
            $sbpCode,
            $reason,
            $httpCode,
            $requestException
        );
    }

    private function verifyResponseSignature(Response $response)
    {
        $signatureHeaderName = 'x-shopware-signature';
        $signature = $response->getHeader($signatureHeaderName);

        if (empty($signature)) {
            throw new \RuntimeException(sprintf('Signature not found in header "%s"', $signatureHeaderName));
        }

        if (!$this->openSSLVerifier->isSystemSupported()) {
            return;
        }

        if ($this->openSSLVerifier->isValid($response->getBody(), $signature)) {
            return;
        }

        throw new \RuntimeException('Signature not valid');
    }
}
