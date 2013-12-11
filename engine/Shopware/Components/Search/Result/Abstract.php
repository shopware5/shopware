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

/**
 */
abstract class Shopware_Components_Search_Result_Abstract implements Shopware_Components_Search_Result_Interface
{
    /**
     * Results of current search approach
     * @var array
     */
    protected $result = array();

    /**
     * Id of current category filter set in search request
     * @var int
     */
    protected $resultCategoryFilter;

    /**
     * List of categories that are affected in current search
     * @var array
     */
    protected $resultCategoriesAffected = array();

    /**
     * List of suppliers that are affected in current search
     * @var array
     */
    protected $resultSuppliersAffected = array();

    /**
     * List of price ranges that are affected in current search
     * @var array
     */
    protected $resultPriceRangesAffected = array();

    /**
     * Result count
     * @var int
     */
    protected $resultCount;

    /**
     * Set search result
     * @param array $results
     */
    public function setResult(array $results)
    {
        $this->result = $results;
    }

    /**
     * Get search results
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set current active category filter
     * @param $categoryId
     */
    public function setCurrentCategoryFilter($categoryId)
    {
        $this->resultCategoryFilter = $categoryId;
    }

    /**
     * Get current set category filter
     * @return mixed
     */
    public function getCurrentCategoryFilter()
    {
        return $this->resultCategoryFilter;
    }

    /**
     * Set affected categories in this search request
     * @param array $categories
     */
    public function setAffectedCategories(array $categories)
    {
        $this->resultCategoriesAffected = $categories;
    }

    /**
     * Get list of affected categories in search request
     * @return array
     */
    public function getAffectedCategories()
    {
        return $this->resultCategoriesAffected;
    }

    /**
     * Set affected price ranges in this search request
     * @param $result
     */
    public function setAffectedPriceRanges($result)
    {
        $this->resultPriceRangesAffected = $result;
    }

    /**
     * Get list of affected price ranges in search request
     * @return array
     */
    public function getAffectedPriceRanges()
    {
        return $this->resultPriceRangesAffected;
    }

    /**
     * Get list of affected suppliers in search result
     * @param $result
     */
    public function setAffectedSuppliers($result)
    {
        $this->resultSuppliersAffected = $result;
    }

    /**
     * Get list of affected suppliers in search request
     * @return array
     */
    public function getAffectedSuppliers()
    {
        return $this->resultSuppliersAffected;
    }

    /**
     * Set result count
     * @param $count int
     */
    public function setResultCount($count)
    {
        $this->resultCount = $count;
    }

    /**
     * Get reuslt count
     * @return int
     */
    public function getResultCount()
    {
        return $this->resultCount;
    }
}
