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

class Shopware_StoreApi_Models_Auth extends Shopware_StoreApi_Models_Model
{
    public function __construct($rawData = array())
    {
        $this->rawData = $rawData;
    }

    public function getShopwareId()
    {
        return $this->rawData['shopwareID'];
    }

    public function getToken()
    {
        return $this->rawData['token'];
    }

    public function getAccountUrl()
    {
        return $this->rawData['accountUrl'];
    }

    public function setShopwareId($shopwareID)
    {
        $this->rawData['shopwareID'] = $shopwareID;
    }

    public function setToken($token)
    {
        $this->rawData['token'] = $token;
    }

    public function setAccountUrl($accountUrl)
    {
        $this->rawData['accountUrl'] = $accountUrl;
    }
}
