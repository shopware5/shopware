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

use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Components\HttpClient\Response;

/**
 * @package Shopware\Bundle\PluginInstallerBundle
 */
interface StoreClientInterface
{
    /**
     * @param string $shopwareId
     * @param string $password
     *
     * @return AccessTokenStruct
     * @throws \Exception
     */
    public function getAccessToken($shopwareId, $password);

    /**
     * @param string $resource
     * @param array  $params
     * @param array  $headers
     *
     * @return array
     * @throws \Exception
     */
    public function doGetRequest($resource, $params = [], $headers = []);

    /**
     * @param AccessTokenStruct $accessToken
     * @param string            $resource
     * @param array             $params
     * @param array             $headers
     *
     * @return array
     * @throws \Exception
     */
    public function doAuthGetRequest(AccessTokenStruct $accessToken, $resource, $params, $headers = []);

    /**
     * @param       $resource
     * @param array $params
     * @param array $headers
     *
     * @return mixed
     * @throws \Exception
     */
    public function doGetRequestRaw($resource, $params = [], $headers = []);

    /**
     * @param AccessTokenStruct $accessToken
     * @param string            $resource
     * @param array             $params
     * @param array             $headers
     *
     * @return array
     * @throws \Exception
     */
    public function doAuthGetRequestRaw(AccessTokenStruct $accessToken, $resource, $params, $headers = []);

    /**
     * @param string $resource
     * @param array  $params
     * @param array  $headers
     *
     * @return array
     * @throws \Exception
     */
    public function doPostRequest($resource, $params, $headers = []);

    /**
     * @param AccessTokenStruct $accessToken
     * @param string            $resource
     * @param array             $params
     *
     * @return array
     * @throws \Exception
     */
    public function doAuthPostRequest(AccessTokenStruct $accessToken, $resource, $params);

    /**
     * @param AccessTokenStruct $accessToken
     * @param                   $resource
     * @param                   $params
     *
     * @return Response
     * @throws \Exception
     */
    public function doAuthPostRequestRaw(AccessTokenStruct $accessToken, $resource, $params);

    /**
     * @return bool
     */
    public function doPing();
}
