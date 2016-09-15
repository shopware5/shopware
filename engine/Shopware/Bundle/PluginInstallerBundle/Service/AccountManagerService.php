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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use GuzzleHttp\ClientInterface;
use Shopware\Bundle\PluginInstallerBundle\Exception\StoreException;
use Shopware\Bundle\PluginInstallerBundle\StoreClientInterface;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydratorInterface;
use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\Model\ModelManager;

/**
 * @package Shopware\Bundle\PluginInstallerBundle\Service
 */
class AccountManagerService implements AccountManagerServiceInterface
{
    /**
     * @var StoreClientInterface
     */
    private $storeClient;

    /**
     * @var StructHydratorInterface
     */
    private $hydrator;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var ClientInterface
     */
    private $guzzleHttpClient;

    /**
     * @var String
     */
    private $apiEndPoint;

    /**
     * @param StoreClientInterface                 $storeClient
     * @param StructHydratorInterface              $structHydrator
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param ModelManager                         $entityManager
     * @param GuzzleFactory                        $guzzleFactory
     * @param String                               $apiEndPoint
     *
     * @internal param ClientInterface $guzzleHttpClient
     */
    public function __construct(
        StoreClientInterface $storeClient,
        StructHydratorInterface $structHydrator,
        \Shopware_Components_Snippet_Manager $snippetManager,
        ModelManager $entityManager,
        GuzzleFactory $guzzleFactory,
        $apiEndPoint
    ) {
        $this->storeClient = $storeClient;
        $this->hydrator = $structHydrator;
        $this->snippetManager = $snippetManager;
        $this->entityManager = $entityManager;
        $this->guzzleHttpClient = $guzzleFactory->createClient();
        $this->apiEndPoint = $apiEndPoint;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        $repo = $this->entityManager->getRepository('Shopware\Models\Shop\Shop');

        $default = $repo->getActiveDefault();

        return $default->getHost();
    }

    /**
     * Pings SBP to see if a connection is available and the service is up
     *
     * @throws \Exception
     * @return boolean
     */
    public function pingServer()
    {
        try {
            return $this->storeClient->doPing();
        } catch (\Exception $e) {
            $snippet = $this->snippetManager
                ->getNamespace('backend/plugin_manager/exceptions')
                ->get('timeout', 'The connection with SBP timed out');

            throw new \Exception($snippet, $e->getCode(), $e);
        }
    }

    /**
     * Requests the creation of a new Shopware ID anc account (registration action)
     *
     * @param string $shopwareId
     * @param string $email
     * @param string $password
     * @param int $localeId
     * @return array
     * @throws \Exception
     */
    public function registerAccount($shopwareId, $email, $password, $localeId)
    {
        $postData = [
            'shopwareId' => $shopwareId,
            'email'      => $email,
            'password'   => $password,
            'localeId'   => $localeId
        ];

        try {
            return $this->storeClient->doPostRequest('/users', $postData);
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }
    }

    /**
     * Gets a list of locales supported by the SBP
     *
     * @return LocaleStruct[] array of locale details
     * @throws \Exception
     */
    public function getLocales()
    {
        try {
            $responseBody = $this->storeClient->doGetRequest("/locales");
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }

        return $this->hydrator->hydrateLocales($responseBody);
    }

    /**
     * Get the list of shops (and details) associated to the given user
     *
     * @param AccessTokenStruct $token
     * @return array Array of shop details
     * @throws \Exception
     */
    public function getShops(AccessTokenStruct $token)
    {
        $query = ['shopwareId' => $token->getShopwareId()];

        try {
            return $this->storeClient->doAuthGetRequest($token, "/shops", $query);
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }
    }

    /**
     * Requests the domain hash and filename needed to generate the
     * validation key, so that the current domain can be validated
     *
     * @param string $domain
     * @param AccessTokenStruct $token
     * @return array Filename and domain hash of the domain validation file
     * @throws \Exception
     */
    public function getDomainHash($domain, AccessTokenStruct $token)
    {
        $postData = ['domain' => $domain];

        try {
            return $this->storeClient->doAuthPostRequest($token, "/domainhashes", $postData);
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }
    }

    /**
     * Requests the validation of the current installation's domain
     *
     * @param string $domain
     * @param string $shopwareVersion Current Shopware version
     * @param AccessTokenStruct $token
     * @return array Result of the validation operation (empty if successful)
     * @throws \Exception
     */
    public function verifyDomain($domain, $shopwareVersion, AccessTokenStruct $token)
    {
        $postData = [
            'shopwareId' => $token->getShopwareId(),
            'domain' => $domain,
            'shopwareVersion' => $shopwareVersion
        ];

        try {
            return $this->storeClient->doAuthPostRequest($token, "/domainverifications", $postData);
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }
    }

    /**
     * Gets an access token from the server using the provided auth credentials
     *
     * @param string $shopwareId
     * @param string $password
     * @return AccessTokenStruct Token to access the API
     * @throws \Exception
     */
    public function getToken($shopwareId = null, $password = null)
    {
        try {
            return $this->storeClient->getAccessToken($shopwareId, $password);
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }
    }

    /**
     * @param StoreException $exception
     * @return \Exception
     */
    private function translateExceptionMessage(StoreException $exception)
    {
        $namespace = $this->snippetManager
            ->getNamespace('backend/plugin_manager/exceptions');

        if ($namespace->offsetExists($exception->getMessage())) {
            $snippet = $namespace->get($exception->getMessage());
        } else {
            $snippet = $exception->getMessage();
        }

        $snippet .= '<br><br>Error code: ' . $exception->getSbpCode();

        return new \Exception($snippet, $exception->getCode(), $exception);
    }
}
