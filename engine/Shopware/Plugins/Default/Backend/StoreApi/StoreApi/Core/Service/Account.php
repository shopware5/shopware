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

class Shopware_StoreApi_Core_Service_Account extends Enlight_Class
{
    /**
     * Holds the gateway of the service layer
     * @var Shopware_StoreApi_Core_Gateway_Account
     */
    private $gateway;

    public function init()
    {
        $this->gateway = new Shopware_StoreApi_Core_Gateway_Account();
    }

    public function getDomain(Shopware_StoreApi_Models_Auth $authModel, $domain)
    {
        if(!$authModel instanceof Shopware_StoreApi_Models_Auth) {
            return new Shopware_StoreApi_Core_Response_SearchResult(array());
        }

        $searchResult = $this->gateway->getDomains($authModel->getShopwareId(), $authModel->getToken());
        if($searchResult instanceof Shopware_StoreApi_Exception_Response) {
            return $searchResult;
        }

        foreach($searchResult as $domainModel) {
            if($domainModel->getDomain() == $domain) {
                return $domainModel;
            }
        }

        return new Shopware_StoreApi_Exception_Response('Out of range', Shopware_StoreApi_Exception_Response::OUT_OF_RANGE);
    }

    public function getDomains(Shopware_StoreApi_Models_Auth $authModel)
    {
        if(!$authModel instanceof Shopware_StoreApi_Models_Auth) {
            throw new Enlight_Exception('The parameter authModel is not an instance of the class Shopware_StoreApi_Models_Auth');
        }

        return $this->gateway->getDomains($authModel->getShopwareId(), $authModel->getToken());
    }

    public function getLicencedProducts(Shopware_StoreApi_Models_Auth $authModel, $domainModel, $version)
    {
        if(!$authModel instanceof Shopware_StoreApi_Models_Auth) {
            throw new Enlight_Exception('The parameter authModel is not an instance of the class Shopware_StoreApi_Models_Auth');
        }elseif(!$domainModel instanceof Shopware_StoreApi_Models_Domain) {
            throw new Enlight_Exception('The parameter domainModel is not an instance of the class Shopware_StoreApi_Models_Domain');
        }elseif(!is_integer($version)) {
            return new Shopware_StoreApi_Exception_Response('The parameter version is not instance of Integer', 10);
        }

        return $this->gateway->getLicencedProducts($authModel->getShopwareId(), $authModel->getToken(), $domainModel->getDomain(), $domainModel->getAccountId(), $version);
    }

    public function getLicencedProductById(Shopware_StoreApi_Models_Auth $authModel, $domainModel, $articleID, $version)
    {
        if(!$authModel instanceof Shopware_StoreApi_Models_Auth) {
            throw new Enlight_Exception('The parameter authModel is not an instance of the class Shopware_StoreApi_Models_Auth');
        }elseif(!$domainModel instanceof Shopware_StoreApi_Models_Domain) {
            throw new Enlight_Exception('The parameter domainModel is not an instance of the class Shopware_StoreApi_Models_Domain');
        }elseif(!is_integer($version)) {
            return new Shopware_StoreApi_Exception_Response('The parameter version is not instance of Integer', 10);
        }

        return $this->gateway->getLicencedProductById($authModel->getShopwareId(), $authModel->getToken(), $domainModel->getDomain(), $domainModel->getAccountId(), $articleID, $version);
    }

    public function getTax(Shopware_StoreApi_Models_Auth $authModel)
    {
        if(!$authModel instanceof Shopware_StoreApi_Models_Auth) {
            throw new Enlight_Exception('The parameter authModel is not an instance of the class Shopware_StoreApi_Models_Auth');
        }

        return $this->gateway->getTax($authModel->getShopwareId(), $authModel->getToken());
    }
}
