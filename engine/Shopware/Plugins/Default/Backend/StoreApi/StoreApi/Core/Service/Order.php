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

class Shopware_StoreApi_Core_Service_Order extends Enlight_Class
{
    /**
     * Holds the gateway of the service layer
     * @var Shopware_StoreApi_Core_Gateway_Order
     */
    private $gateway;

    public function init()
    {
        $this->gateway = new Shopware_StoreApi_Core_Gateway_Order();
    }

    public function orderProduct($authModel, $domainModel, $productModel, $rentVersion=false)
    {
        if (!$authModel instanceof Shopware_StoreApi_Models_Auth) {
            throw new Enlight_Exception('The parameter authModel is not an instance of the class Shopware_StoreApi_Models_Auth');
        } elseif (!$domainModel instanceof Shopware_StoreApi_Models_Domain) {
            throw new Enlight_Exception('The parameter domainModel is not an instance of the class Shopware_StoreApi_Models_Domain');
        } elseif (!$productModel instanceof Shopware_StoreApi_Models_Product) {
            throw new Enlight_Exception('The parameter productModel is not an instance of the class Shopware_StoreApi_Models_Product');
        }

        return $this->gateway->orderProduct(
            $authModel->getShopwareId(),
            $authModel->getToken(),
            $domainModel->getDomain(),
            $domainModel->getAccountId(),
            $productModel->getId(),
            $rentVersion == true ? 1 : 0
        );
    }
}
