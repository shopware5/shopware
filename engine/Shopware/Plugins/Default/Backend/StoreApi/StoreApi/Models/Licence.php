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

class Shopware_StoreApi_Models_Licence extends Shopware_StoreApi_Models_Model
{
    public function __construct($rawData = array())
    {
        $this->rawData = $rawData;
    }

    public function getId()
    {
        return $this->rawData['id'];
    }

    public function getOrdernumber()
    {
        return $this->rawData['ordernumber'];
    }

    public function getKey()
    {
        return $this->rawData['key'];
    }

    public function getLicence()
    {
        return $this->rawData['licence'];
    }

    public function getExpireDate()
    {
        return $this->rawData['licence'];
    }

    public function getDownloads()
    {
        return $this->rawData['downloads'];
    }

    public function isOfficialModule()
    {
        return !empty($this->rawData['official_module']);
    }

    public function isPayedLicence()
    {
        return !empty($this->rawData['payed']);
    }

    public function isRentLicence()
    {
        return !empty($this->rawData['rent']);
    }

    public function isTrialLicence()
    {
        return !empty($this->rawData['trial']);
    }
}
