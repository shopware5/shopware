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

use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\StructHydrator;

/**
 * @package Shopware\Bundle\PluginInstallerBundle\Service
 */
class AccountManagerService
{
    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var StructHydrator
     */
    private $hydrator;

    /**
     * @param StoreClient $storeClient
     * @param StructHydrator $structHydrator
     */
    public function __construct(StoreClient $storeClient, StructHydrator $structHydrator)
    {
        $this->storeClient = $storeClient;
        $this->hydrator = $structHydrator;
    }

    /**
     * Pings SBP to see if a connection is available and the service is up
     *
     * @throws \Exception
     * @return boolean
     */
    public function pingServer()
    {
        return $this->storeClient->doGetRequest('/ping') ? : false;
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
        $postData = array(
            'shopwareId' => $shopwareId,
            'email'      => $email,
            'password'   => $password,
            'localeId'   => $localeId
        );

        return $this->storeClient->doPostRequest('/users', $postData);
    }

    /**
     * Gets a list of locales supported by the SBP
     *
     * @return LocaleStruct[] array of locale details
     * @throws \Exception
     */
    public function getLocales()
    {
        $responseBody = $this->storeClient->doGetRequest("/locales");

        return $this->hydrator->hydrateLocales($responseBody);
    }

    /**
     * Get the list of shops (and details) associated to the given user
     *
     * @param AccessTokenStruct $token
     * @param string $shopwareId
     * @return Obj[] array of shop details
     * @throws \Exception
     */
    public function getShops(AccessTokenStruct $token, $shopwareId)
    {
        $query = ['shopwareId' => $shopwareId];

        return $this->storeClient->doAuthGetRequest($token, "/shops", $query);
    }

    /**
     * Requests the domain hash and filename needed to generate the
     * validation key, so that the current domain can be validated
     *
     * @param string $domain
     * @param AccessTokenStruct $token
     * @return array Filename and domain hash of the domain validation file
     * @throws \RuntimeException
     */
    public function getDomainHash($domain, AccessTokenStruct $token)
    {
        $postData = array(
            'domain' => $domain,
        );

        return $this->storeClient->doAuthPostRequest($token, "/domainhashes", $postData);
    }

    /**
     * Requests the validation of the current installation's domain
     *
     * @param string $domain
     * @param string $shopwareId
     * @param AccessTokenStruct $token
     * @return array Result of the validation operation (empty if successful)
     * @throws \RuntimeException
     */
    public function verifyDomain($domain, $shopwareId, AccessTokenStruct $token)
    {
        $postData = array(
            'shopwareId' => $shopwareId,
            'domain' => $domain
        );

        return $this->storeClient->doAuthPostRequest($token, "/domainverifications", $postData);
    }

    /**
     * Gets an access token from the server using the provided auth credentials
     *
     * @param string $shopwareId
     * @param string $password
     * @return AccessTokenStruct Token to access the API
     * @throws \RuntimeException
     */
    public function getToken($shopwareId = null, $password = null)
    {
        return $this->storeClient->getAccessToken($shopwareId, $password);
    }
}
