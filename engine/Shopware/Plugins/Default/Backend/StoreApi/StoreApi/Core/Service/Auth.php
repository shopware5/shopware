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

class Shopware_StoreApi_Core_Service_Auth extends Enlight_Class
{
    /**
     * Holds the gateway of the service layer
     * @var Shopware_StoreApi_Core_Gateway_Auth
     */
    private $gateway;

    public function init()
    {
        $this->gateway = new Shopware_StoreApi_Core_Gateway_Auth();
    }

    public function login($shopwareID, $password)
    {
        return $this->gateway->login($shopwareID, $password);
    }

    public function isTokenValid(Shopware_StoreApi_Models_Auth $authModel)
    {
        if (!$authModel instanceof Shopware_StoreApi_Models_Auth) {
            return false;
        }

        $array = $this->gateway->isTokenValid($authModel->getShopwareId(), $authModel->getToken());
        if ($array['authorized'] == true) {
            return true;
        } else {
            return false;
        }


    }
}
