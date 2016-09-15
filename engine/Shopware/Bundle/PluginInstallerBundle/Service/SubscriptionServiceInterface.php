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

use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_Response_ResponseHttp as Response;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginInformationResultStruct;

/**
 * Class SubscriptionService
 *
 * @package Shopware\Bundle\PluginInstallerBundle\Service
 */
interface SubscriptionServiceInterface
{
    /**
     * reset the Secret in the database
     */
    public function resetShopSecret();

    /**
     * get current secret from the database
     *
     * @return string
     */
    public function getShopSecret();

    /**
     * set new secret to the database
     */
    public function setShopSecret();

    /**
     * Returns information about shop upgrade state and installed plugins.
     *
     * @param Response $response
     * @param Request  $request
     *
     * @return PluginInformationResultStruct|bool
     */
    public function getPluginInformation(Response $response, Request $request);
}
