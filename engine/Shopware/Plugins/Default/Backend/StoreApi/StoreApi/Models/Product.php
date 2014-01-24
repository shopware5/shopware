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

class Shopware_StoreApi_Models_Product extends Shopware_StoreApi_Models_Model
{
    public function getId()
    {
        return $this->rawData['id'];
    }

    public function getName()
    {
        return $this->rawData['name'];
    }

    public function getPluginNames()
    {
        return $this->rawData['plugin_names'];
    }

    public function getOrdernumber()
    {
        return $this->rawData['details']['main']['ordernumber'];
    }

    public function getDescription()
    {
        return $this->rawData['description'];
    }

    public function isRentable()
    {
        if (!empty($this->rawData['details']['rent'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getDetails()
    {
        $details = array();

        $main = $this->rawData['details']['main'];
        $main['rent_version'] = false;
        $details[] = $main;

       if ($this->isRentable() === true) {
           $rent = $this->rawData['details']['rent'];
           $rent['rent_version'] = true;
           $details[] = $rent;
       }

       return $details;
    }

    public function isTestable()
    {
        if (!empty($this->rawData['attributes']['test_modus'])) {
            return true;
        } else {
            return false;
        }
    }

    public function isCertified()
    {
        if (!empty($this->rawData['addons']['certification'])) {
            return true;
        } else {
            return false;
        }
    }

    public function isEncrypted()
    {
        if (!empty($this->rawData['addons']['encryption'])) {
            return true;
        } else {
            return false;
        }
    }

    public function isHighlighted()
    {
        if (!empty($this->rawData['addons']['highlight'])) {
            return true;
        } else {
            return false;
        }
    }

    public function isHighlightedAsBanner()
    {
        if (!empty($this->rawData['addons']['banner'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getChangelog()
    {
        return $this->rawData['attributes']['changelog'];
    }

    public function getLicenceKey()
    {
        return $this->rawData['attributes']['licence_key'];
    }

    public function getSupportBy()
    {
        return $this->rawData['attributes']['support_by'];
    }

    public function getForumUrl()
    {
        return $this->rawData['attributes']['forum_url'];
        }

    public function getStoreUrl()
    {
        return $this->rawData['attributes']['store_url'];
    }

    public function getInstallDescription()
    {
        return $this->rawData['attributes']['install_description'];
    }

    public function getShopwareCompatible()
    {
        return $this->rawData['attributes']['shopware_compatible'];
    }

    public function getVersion()
    {
        return $this->rawData['attributes']['version'];
    }

    public function getImages()
    {
        return $this->rawData['images'];
    }

    public function getCategories()
    {
        return $this->rawData['categories'];
    }
}
