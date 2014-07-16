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

class Shopware_StoreApi_Core_Gateway_Account extends Shopware_StoreApi_Core_Gateway_Gateway
{
    public function getDomains($shopwareID, $token)
    {
        $json = array(
            'shopwareID' => $shopwareID,
            'token' => $token
        );

        return $this->get('account/domains', $json);
    }

    public function getLicencedProducts($shopwareID, $token, $domain, $accountID, $version)
    {
        $json = array(
            'domain' => $domain,
            'accountID' => $accountID,
            'shopwareID' => $shopwareID,
            'token' => $token,
            'version' => $version
        );

        return $this->get('account/licenced', $json);
    }

    public function getLicencedProductById($shopwareID, $token, $domain, $accountID, $articleID, $version)
    {
        $json = array(
            'domain' => $domain,
            'accountID' => $accountID,
            'shopwareID' => $shopwareID,
            'token' => $token,
            'id' => $articleID,
            'version' => $version
        );

        return $this->get('account/licencedOne', $json);
    }

    public function getTax($shopwareID, $token)
    {
        $json = array(
            'shopwareID' => $shopwareID,
            'token' => $token
        );

        return $this->get('account/tax', $json);
    }
}
