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

namespace Shopware\Bundle\StoreFrontBundle\Product;

use Shopware\Bundle\StoreFrontBundle\Category\Category;
use Shopware\Bundle\StoreFrontBundle\Price\Price;
use Shopware\Bundle\StoreFrontBundle\Price\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Vote\VoteAverage;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ListProduct extends SimpleProduct
{
    /**
     * State for a calculated product price.
     */
    const STATE_PRICE_CALCULATED = 'price_calculated';

    /**
     * Contains the absolute cheapest price of each product variation.
     * This price is calculated over the shopware price service
     * getCheapestPrice function.
     *
     * @var \Shopware\Bundle\StoreFrontBundle\Price\Price
     */
    protected $cheapestPrice;

    /**
     * Contains the price rule for the cheapest price.
     *
     * @var \Shopware\Bundle\StoreFrontBundle\Price\PriceRule
     */
    protected $cheapestPriceRule;

    /**
     * Contains the cheapest unit price of each product variation.
     *
     * @var Price
     */
    protected $cheapestUnitPrice;

    /**
     * @var PriceRule[]
     */
    protected $priceRules = [];

    /**
     * Price of the current variant.
     *
     * @var Price[]
     */
    protected $prices = [];

    /**
     * @var VoteAverage
     */
    protected $voteAverage;

    /**
     * @var int
     */
    protected $customerPriceCount;

    /**
     * @var int
     */
    protected $fallbackPriceCount;

    /**
     * @var Category[]
     */
    protected $categories = [];

    /**
     * @var Price
     */
    protected $listingPrice;

    /**
     * @var bool
     */
    protected $allowBuyInListing;

    /**
     * @var bool
     */
    protected $displayFromPrice;

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Price\Price[] $prices
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Price\Price[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Price\Price $cheapestPrice
     */
    public function setCheapestPrice($cheapestPrice)
    {
        $this->cheapestPrice = $cheapestPrice;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Price\Price
     */
    public function getCheapestPrice()
    {
        return $this->cheapestPrice;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Price\Price
     */
    public function getVariantPrice()
    {
        return $this->prices[0];
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Price\PriceRule[]
     */
    public function getPriceRules()
    {
        return $this->priceRules;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Price\PriceRule[] $priceRules
     */
    public function setPriceRules($priceRules)
    {
        $this->priceRules = $priceRules;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Price\PriceRule
     */
    public function getCheapestPriceRule()
    {
        return $this->cheapestPriceRule;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Price\PriceRule $cheapestPriceRule
     */
    public function setCheapestPriceRule($cheapestPriceRule)
    {
        $this->cheapestPriceRule = $cheapestPriceRule;
    }

    /**
     * @return VoteAverage
     */
    public function getVoteAverage()
    {
        return $this->voteAverage;
    }

    /**
     * @param VoteAverage $voteAverage
     */
    public function setVoteAverage($voteAverage)
    {
        $this->voteAverage = $voteAverage;
    }

    /**
     * @return Price
     */
    public function getCheapestUnitPrice()
    {
        return $this->cheapestUnitPrice;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Price\Price $cheapestUnitPrice
     */
    public function setCheapestUnitPrice($cheapestUnitPrice)
    {
        $this->cheapestUnitPrice = $cheapestUnitPrice;
    }

    /**
     * @return int
     */
    public function getCustomerPriceCount()
    {
        return $this->customerPriceCount;
    }

    /**
     * @param int $customerPriceCount
     */
    public function setCustomerPriceCount($customerPriceCount)
    {
        $this->customerPriceCount = $customerPriceCount;
    }

    /**
     * @return int
     */
    public function getFallbackPriceCount()
    {
        return $this->fallbackPriceCount;
    }

    /**
     * @param int $fallbackPriceCount
     */
    public function setFallbackPriceCount($fallbackPriceCount)
    {
        $this->fallbackPriceCount = $fallbackPriceCount;
    }

    /**
     * @return bool
     */
    public function hasDifferentPrices()
    {
        return
            $this->getCustomerPriceCount() > 1
            ||
            $this->getFallbackPriceCount() > 1
        ;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Category[] $categories
     */
    public function setCategories(array $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @param Price $price
     */
    public function setListingPrice(Price $price)
    {
        $this->listingPrice = $price;
    }

    /**
     * @return Price
     */
    public function getListingPrice()
    {
        return $this->listingPrice;
    }

    /**
     * @return bool
     */
    public function displayFromPrice()
    {
        return $this->displayFromPrice;
    }

    /**
     * @return bool
     */
    public function allowBuyInListing()
    {
        return $this->allowBuyInListing;
    }

    /**
     * @param bool $allowBuyInListing
     */
    public function setAllowBuyInListing($allowBuyInListing)
    {
        $this->allowBuyInListing = $allowBuyInListing;
    }

    /**
     * @param bool $displayFromPrice
     */
    public function setDisplayFromPrice($displayFromPrice)
    {
        $this->displayFromPrice = $displayFromPrice;
    }
}
