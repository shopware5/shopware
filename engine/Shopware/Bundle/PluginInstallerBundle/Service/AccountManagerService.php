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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Exception;
use Shopware\Bundle\PluginInstallerBundle\Exception\StoreException;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Snippet_Manager;

class AccountManagerService
{
    private StoreClient $storeClient;

    private StructHydrator $hydrator;

    private Shopware_Components_Snippet_Manager $snippetManager;

    private ModelManager $entityManager;

    public function __construct(
        StoreClient $storeClient,
        StructHydrator $structHydrator,
        Shopware_Components_Snippet_Manager $snippetManager,
        ModelManager $entityManager
    ) {
        $this->storeClient = $storeClient;
        $this->hydrator = $structHydrator;
        $this->snippetManager = $snippetManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        $default = $this->entityManager->getRepository(Shop::class)->getActiveDefault();

        return (string) $default->getHost();
    }

    /**
     * Pings SBP to see if a connection is available and the service is up
     *
     * @throws Exception
     *
     * @return bool
     */
    public function pingServer()
    {
        try {
            return $this->storeClient->doPing();
        } catch (Exception $e) {
            $snippet = $this->snippetManager
                ->getNamespace('backend/plugin_manager/exceptions')
                ->get('timeout', 'The connection with SBP timed out');

            throw new Exception($snippet, $e->getCode(), $e);
        }
    }

    /**
     * Requests the creation of a new Shopware ID anc account (registration action)
     *
     * @deprecated since 5.7.8, will be removed in 5.8 without replacement.
     *
     * @param string $shopwareId
     * @param string $email
     * @param string $password
     * @param int    $localeId
     *
     * @throws Exception
     *
     * @return array
     */
    public function registerAccount($shopwareId, $email, $password, $localeId)
    {
        $postData = [
            'shopwareId' => $shopwareId,
            'email' => $email,
            'password' => $password,
            'localeId' => $localeId,
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
     * @throws Exception
     *
     * @return LocaleStruct[] array of locale details
     */
    public function getLocales()
    {
        try {
            $responseBody = $this->storeClient->doGetRequest('/locales');
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }

        return $this->hydrator->hydrateLocales($responseBody);
    }

    /**
     * Get the list of shops (and details) associated to the given user
     *
     * @throws Exception
     *
     * @return array Array of shop details
     */
    public function getShops(AccessTokenStruct $token)
    {
        $query = ['shopwareId' => $token->getShopwareId()];

        try {
            return $this->storeClient->doAuthGetRequest($token, '/shops', $query);
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }
    }

    /**
     * Requests the domain hash and filename needed to generate the
     * validation key, so that the current domain can be validated
     *
     * @param string $domain
     *
     * @throws Exception
     *
     * @return array Filename and domain hash of the domain validation file
     */
    public function getDomainHash($domain, AccessTokenStruct $token)
    {
        $postData = ['domain' => $domain];

        try {
            return $this->storeClient->doAuthPostRequest($token, '/domainhashes', $postData);
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }
    }

    /**
     * Requests the validation of the current installation's domain
     *
     * @param string $domain
     * @param string $shopwareVersion Current Shopware version
     *
     * @throws Exception
     *
     * @return array Result of the validation operation (empty if successful)
     */
    public function verifyDomain($domain, $shopwareVersion, AccessTokenStruct $token)
    {
        $postData = [
            'shopwareId' => $token->getShopwareId(),
            'domain' => $domain,
            'shopwareVersion' => $shopwareVersion,
        ];

        try {
            return $this->storeClient->doAuthPostRequest($token, '/domainverifications', $postData);
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }
    }

    /**
     * Gets an access token from the server using the provided auth credentials
     *
     * @param string $shopwareId
     * @param string $password
     *
     * @throws Exception
     *
     * @return AccessTokenStruct Token to access the API
     */
    public function getToken($shopwareId = null, $password = null)
    {
        try {
            return $this->storeClient->getAccessToken($shopwareId, $password);
        } catch (StoreException $se) {
            throw $this->translateExceptionMessage($se);
        }
    }

    private function translateExceptionMessage(StoreException $exception): Exception
    {
        $namespace = $this->snippetManager
            ->getNamespace('backend/plugin_manager/exceptions');

        if ($namespace->offsetExists($exception->getMessage())) {
            $snippet = $namespace->get($exception->getMessage());
        } else {
            $snippet = $exception->getMessage();
        }

        $snippet .= '<br><br>Error code: ' . $exception->getSbpCode();

        return new Exception($snippet, $exception->getCode(), $exception);
    }
}
