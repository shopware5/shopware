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

use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LocaleStruct;

/**
 * @package Shopware\Bundle\PluginInstallerBundle\Service
 */
interface AccountManagerServiceInterface
{
    /**
     * @return string
     */
    public function getDomain();

    /**
     * Pings SBP to see if a connection is available and the service is up
     *
     * @throws \Exception
     * @return boolean
     */
    public function pingServer();

    /**
     * Requests the creation of a new Shopware ID anc account (registration action)
     *
     * @param string $shopwareId
     * @param string $email
     * @param string $password
     * @param int    $localeId
     *
     * @return array
     * @throws \Exception
     */
    public function registerAccount($shopwareId, $email, $password, $localeId);

    /**
     * Gets a list of locales supported by the SBP
     *
     * @return LocaleStruct[] array of locale details
     * @throws \Exception
     */
    public function getLocales();

    /**
     * Get the list of shops (and details) associated to the given user
     *
     * @param AccessTokenStruct $token
     *
     * @return array Array of shop details
     * @throws \Exception
     */
    public function getShops(AccessTokenStruct $token);

    /**
     * Requests the domain hash and filename needed to generate the
     * validation key, so that the current domain can be validated
     *
     * @param string            $domain
     * @param AccessTokenStruct $token
     *
     * @return array Filename and domain hash of the domain validation file
     * @throws \Exception
     */
    public function getDomainHash($domain, AccessTokenStruct $token);

    /**
     * Requests the validation of the current installation's domain
     *
     * @param string            $domain
     * @param string            $shopwareVersion Current Shopware version
     * @param AccessTokenStruct $token
     *
     * @return array Result of the validation operation (empty if successful)
     * @throws \Exception
     */
    public function verifyDomain($domain, $shopwareVersion, AccessTokenStruct $token);

    /**
     * Gets an access token from the server using the provided auth credentials
     *
     * @param string $shopwareId
     * @param string $password
     *
     * @return AccessTokenStruct Token to access the API
     * @throws \Exception
     */
    public function getToken($shopwareId = null, $password = null);
}
