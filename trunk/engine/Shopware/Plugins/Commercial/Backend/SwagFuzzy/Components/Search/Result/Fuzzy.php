<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Components_Search
 * @subpackage Result
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */

/**
 * todo@all: Documentation
 */
class Shopware_Components_Search_Result_Fuzzy extends Shopware_Components_Search_Result_Default {

    /**
     * Property groups
     * @var array
     */
    public $resultPropertiesAffected;
    /**
     * Property values
     * @var array
     */
    public $resultPropertyValuesAffected;

    /**
     * Property options
     * @var array
     */
    public $resultPropertyOptionsAffected;

    /**
     * Similar search terms
     * @var array
     */
    public $resultMatchingKeywords;

    /**
     * Similar search requests
     * @var array
     */
    public $resultMatchingSearchRequests;

    /**
     * Set matching search requests
     * @param $resultMatchingSearchRequests
     */
    public function setResultMatchingSearchRequests($resultMatchingSearchRequests)
    {
        $this->resultMatchingSearchRequests = $resultMatchingSearchRequests;
    }

    /**
     * Get matching search requests
     * @return array
     */
    public function getResultMatchingSearchRequests()
    {
        return $this->resultMatchingSearchRequests;
    }

    /**
     * Set matching terms
     * @param $resultMatchingKeywords
     */
    public function setResultMatchingKeywords($resultMatchingKeywords)
    {
        $this->resultMatchingKeywords = $resultMatchingKeywords;
    }

    /**
     * Get matching terms
     * @return array
     */
    public function getResultMatchingKeywords()
    {
        return $this->resultMatchingKeywords;
    }

    /**
     * Set affected property groups
     * @param $properties
     */
    public function setAffectedProperties($properties){
        $this->resultPropertiesAffected = $properties;
    }

    /**
     * Get affected property groups
     * @return array
     */
    public function getAffectedProperties(){
        return $this->resultPropertiesAffected;
    }

    /**
     * Set affected property options
     * @param $options
     */
    public function setAffectedPropertyOptions($options){
        $this->resultPropertyOptionsAffected = $options;
    }

    /**
     * Get affected property options
     * @return array
     */
    public function getAffectedPropertyOptions(){
        return $this->resultPropertyOptionsAffected;
    }

    /**
     * Set affected property values
     * @param $values
     */
    public function setAffectedPropertyValues($values){
        $this->resultPropertyValuesAffected = $values;
    }

    /**
     * Get affected property values
     * @return array
     */
    public function getAffectedPropertyValues(){
        return $this->resultPropertyValuesAffected;
    }
}