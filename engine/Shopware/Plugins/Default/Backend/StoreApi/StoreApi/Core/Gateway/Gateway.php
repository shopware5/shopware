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

abstract class Shopware_StoreApi_Core_Gateway_Gateway extends Enlight_Class
{
    /**
     * @var Shopware_StoreApi_Core_Rest_Client
     */
    private $client;

    public function init()
    {
        //Gets the instance of the store api client
        $this->client = Shopware()->StoreApi()->getClient();
    }

    /**
     * @return Shopware_StoreApi_Core_Rest_Client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function get($url, $json)
    {
        return $this->getClient()->call(Shopware_StoreApi_Core_Rest_Client::TYPE_GET, $url, $json);
    }
}
