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

class Shopware_StoreApi_Core_Response_Response implements IteratorAggregate
{
    const TYPE_SEARCH_RESULT = '_search_result';
    const TYPE_PRODUCTS = '_products';
    const TYPE_CATEGORIES = '_categories';
    const TYPE_VENDORS = '_vendors';
    const TYPE_DOMAINS = '_domains';
    const TYPE_AUTH = '_auth';
    const TYPE_LICENCE = '_licence';
    const TYPE_ORDER = '_order';
    const TYPE_TAX = '_tax';
    const TYPE_FEEDBACK = '_feedback';
    const TYPE_ARRAY = '_array';

    public $collection;

    public function __construct($response, $decode = true)
    {
        $this->collection = array();

        foreach($response as $model => $json) {
            if($decode === true) {
                $responseArray = Zend_Json::decode($json);
            } else {
                $responseArray = $json;
            }
            switch($model) {
                case self::TYPE_SEARCH_RESULT:
                    $this->collection[] = new Shopware_StoreApi_Core_Response_SearchResult($responseArray);
                break;
                case self::TYPE_PRODUCTS:
                    foreach($responseArray as $responseItem) {
                        $this->collection[] = new Shopware_StoreApi_Models_Product($responseItem);
                    }
                break;
                case self::TYPE_CATEGORIES:
                    foreach($responseArray as $responseItem) {
                        $this->collection[] = new Shopware_StoreApi_Models_Category($responseItem);
                    }
                break;
                case self::TYPE_VENDORS:
                    foreach($responseArray as $responseItem) {
                        $this->collection[] = new Shopware_StoreApi_Models_Vendor($responseItem);
                    }
                break;
                case self::TYPE_DOMAINS:
                    foreach($responseArray as $responseItem) {
                        $this->collection[] = new Shopware_StoreApi_Models_Domain($responseItem);
                    }
                break;
                case self::TYPE_LICENCE:
                    foreach($responseArray as $responseItem) {
                        $this->collection[] = new Shopware_StoreApi_Models_Licence($responseItem);
                    }
                break;
                case self::TYPE_FEEDBACK:
                    foreach($responseArray as $responseItem) {
                        $this->collection[] = new Shopware_StoreApi_Models_Feedback($responseItem);
                    }
                break;
                case self::TYPE_AUTH:
                    $this->collection[] = new Shopware_StoreApi_Models_Auth($responseArray);
                break;
                case self::TYPE_ORDER:
                    $this->collection[] = new Shopware_StoreApi_Models_Order($responseArray);
                break;
                case self::TYPE_TAX:
                    $this->collection[] = new Shopware_StoreApi_Models_Tax($responseArray);
                break;
                case self::TYPE_ARRAY:
                    $this->collection[] = $responseArray;
                break;
            }
        }
    }

    public function getIterator()
    {
        return new ArrayIterator( $this->collection );
    }

    public function getCollection()
    {
        return $this->collection;
    }
}
