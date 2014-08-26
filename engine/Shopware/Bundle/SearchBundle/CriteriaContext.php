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

namespace Shopware\Bundle\SearchBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CriteriaContext extends Extendable
{
    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $sort;

    /**
     * @var int
     */
    private $category;

    /**
     * @var float
     */
    private $minPrice;

    /**
     * @var float
     */
    private $maxPrice;

    /**
     * @var boolean
     */
    private $shippingFree;

    /**
     * @var boolean
     */
    private $immediateDelivery;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var int[]
     */
    private $manufacturers;

    /**
     * @var string
     */
    private $search;

    /**
     * @var int
     */
    private $voteAverage;

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param float $minPrice
     */
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;
    }

    /**
     * @return float
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @param float $maxPrice
     */
    public function setMaxPrice($maxPrice)
    {
        $this->maxPrice = $maxPrice;
    }

    /**
     * @return float
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * @param int[] $manufacturers
     */
    public function setManufacturers($manufacturers)
    {
        $this->manufacturers = $manufacturers;
    }

    /**
     * @return int[]
     */
    public function getManufacturers()
    {
        return $this->manufacturers;
    }

    /**
     * @param array $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param int $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param boolean $shippingFree
     */
    public function setShippingFree($shippingFree)
    {
        $this->shippingFree = $shippingFree;
    }

    /**
     * @param int $voteAverage
     */
    public function setVoteAverage($voteAverage)
    {
        $this->voteAverage = $voteAverage;
    }

    /**
     * @return boolean
     */
    public function getShippingFree()
    {
        return $this->shippingFree;
    }

    /**
     * @param boolean $immediateDelivery
     */
    public function setImmediateDelivery($immediateDelivery)
    {
        $this->immediateDelivery = $immediateDelivery;
    }

    /**
     * @return boolean
     */
    public function getImmediateDelivery()
    {
        return $this->immediateDelivery;
    }

    /**
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param int $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param string $search
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

    /**
     * @return int
     */
    public function getVoteAverage()
    {
        return $this->voteAverage;
    }
}
