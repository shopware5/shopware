<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\Indexing;

use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class CustomerOrder extends Extendable
{
    /**
     * @var int
     */
    protected $orderCount;

    /**
     * @var float
     */
    protected $totalAmount;

    /**
     * @var float
     */
    protected $avgAmount;

    /**
     * @var float
     */
    protected $minAmount;

    /**
     * @var float
     */
    protected $maxAmount;

    /**
     * @var \DateTime|null
     */
    protected $firstOrderTime;

    /**
     * @var \DateTime|null
     */
    protected $lastOrderTime;

    /**
     * @var float
     */
    protected $avgProductPrice;

    /**
     * @var bool
     */
    protected $hasCanceledOrders;

    /**
     * @var int[]
     */
    protected $payments;

    /**
     * @var int[]
     */
    protected $dispatches;

    /**
     * @var string[]
     */
    protected $devices;

    /**
     * @var int[]
     */
    protected $shops;

    /**
     * @var string[]
     */
    protected $weekdays;

    /**
     * @var string[]
     */
    protected $products;

    /**
     * @var int[]
     */
    protected $manufacturers;

    /**
     * @var int[]
     */
    protected $categories;

    /**
     * @return int
     */
    public function getOrderCount()
    {
        return $this->orderCount;
    }

    /**
     * @param int $orderCount
     */
    public function setOrderCount($orderCount)
    {
        $this->orderCount = $orderCount;
    }

    /**
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param float $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    }

    /**
     * @return float
     */
    public function getAvgAmount()
    {
        return $this->avgAmount;
    }

    /**
     * @param float $avgAmount
     */
    public function setAvgAmount($avgAmount)
    {
        $this->avgAmount = $avgAmount;
    }

    /**
     * @return float
     */
    public function getMinAmount()
    {
        return $this->minAmount;
    }

    /**
     * @param float $minAmount
     */
    public function setMinAmount($minAmount)
    {
        $this->minAmount = $minAmount;
    }

    /**
     * @return float
     */
    public function getMaxAmount()
    {
        return $this->maxAmount;
    }

    /**
     * @param float $maxAmount
     */
    public function setMaxAmount($maxAmount)
    {
        $this->maxAmount = $maxAmount;
    }

    /**
     * @return \DateTime|null
     */
    public function getFirstOrderTime()
    {
        return $this->firstOrderTime;
    }

    /**
     * @param \DateTime|null $firstOrderTime
     */
    public function setFirstOrderTime($firstOrderTime)
    {
        $this->firstOrderTime = $firstOrderTime;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastOrderTime()
    {
        return $this->lastOrderTime;
    }

    /**
     * @param \DateTime|null $lastOrderTime
     */
    public function setLastOrderTime($lastOrderTime)
    {
        $this->lastOrderTime = $lastOrderTime;
    }

    /**
     * @return float
     */
    public function getAvgProductPrice()
    {
        return $this->avgProductPrice;
    }

    /**
     * @param float $avgProductPrice
     */
    public function setAvgProductPrice($avgProductPrice)
    {
        $this->avgProductPrice = $avgProductPrice;
    }

    /**
     * @return bool
     */
    public function hasCanceledOrders()
    {
        return $this->hasCanceledOrders;
    }

    /**
     * @param bool $hasCanceledOrders
     */
    public function setHasCanceledOrders($hasCanceledOrders)
    {
        $this->hasCanceledOrders = $hasCanceledOrders;
    }

    /**
     * @return int[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param int[] $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }

    /**
     * @return int[]
     */
    public function getDispatches()
    {
        return $this->dispatches;
    }

    /**
     * @param int[] $dispatches
     */
    public function setDispatches($dispatches)
    {
        $this->dispatches = $dispatches;
    }

    /**
     * @return string[]
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * @param string[] $devices
     */
    public function setDevices($devices)
    {
        $this->devices = $devices;
    }

    /**
     * @return int[]
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param int[] $shops
     */
    public function setShops($shops)
    {
        $this->shops = $shops;
    }

    /**
     * @return string[]
     */
    public function getWeekdays()
    {
        return $this->weekdays;
    }

    /**
     * @param string[] $weekdays
     */
    public function setWeekdays($weekdays)
    {
        $this->weekdays = $weekdays;
    }

    /**
     * @return string[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @var string[]
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @return int[]
     */
    public function getManufacturers()
    {
        return $this->manufacturers;
    }

    /**
     * @var int[]
     */
    public function setManufacturers($manufacturers)
    {
        $this->manufacturers = $manufacturers;
    }

    /**
     * @return int[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @var int[]
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }
}
