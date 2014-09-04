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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Struct
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Context extends Extendable implements \JsonSerializable
{
    /**
     * @var Tax[]
     */
    protected $taxRules;

    /**
     * Contains the current customer group for the store front.
     * If the customer isn't logged in, the current customer group
     * is equal to the fallback customer group of the shop.
     *
     * @var Group
     */
    protected $currentCustomerGroup;

    /**
     * Contains the fallback customer group for the current shop.
     * This customer group is required for price selections.
     * If the customer group of the logged in customer has no
     * own defined product prices, the prices of the fallback customer
     * group are displayed.
     *
     * @var Group
     */
    protected $fallbackCustomerGroup;

    /**
     * Contains the currency of the store front.
     * This struct is required for the price calculation.
     *
     * For example, the shop prices are defined in Euro,
     * the current store front displays Dollars.
     * The currency is required to calculate the Dollar
     * value of 100,- Euro.
     *
     * @var Currency
     */
    protected $currency;

    /**
     * Contains the current shop object of the store front.
     * The shop is used to build links or to select the
     * resource translations.
     *
     * @var Shop
     */
    protected $shop;

    /**
     * @var Area
     */
    protected $area;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Currency $currency
     *
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group $currentCustomerGroup
     * @return $this
     */
    public function setCurrentCustomerGroup($currentCustomerGroup)
    {
        $this->currentCustomerGroup = $currentCustomerGroup;

        return $this;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group
     */
    public function getCurrentCustomerGroup()
    {
        return $this->currentCustomerGroup;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group $fallbackCustomerGroup
     * @return $this
     */
    public function setFallbackCustomerGroup($fallbackCustomerGroup)
    {
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;

        return $this;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group
     */
    public function getFallbackCustomerGroup()
    {
        return $this->fallbackCustomerGroup;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Shop $shop
     * @return $this
     */
    public function setShop($shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Country\Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Country\Area $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Country $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Country\State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Country\State $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Tax[]
     */
    public function getTaxRules()
    {
        return $this->taxRules;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Tax[] $taxRules
     */
    public function setTaxRules($taxRules)
    {
        $this->taxRules = $taxRules;
    }

    /**
     * @param $taxId
     * @return Tax
     */
    public function getTaxRule($taxId)
    {
        $key = 'tax_' . $taxId;

        return $this->taxRules[$key];
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
