<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Context extends Extendable
{
    /**
     * @var Tax[]
     */
    private $taxRules;

    /**
     * Contains the current customer group for the store front.
     * If the customer isn't logged in, the current customer group
     * is equal to the fallback customer group of the shop.
     *
     * @var CustomerGroup
     */
    private $currentCustomerGroup;

    /**
     * Contains the fallback customer group for the current shop.
     * This customer group is required for price selections.
     * If the customer group of the logged in customer has no
     * own defined product prices, the prices of the fallback customer
     * group are displayed.
     *
     * @var CustomerGroup
     */
    private $fallbackCustomerGroup;

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
    private $currency;

    /**
     * Contains the current shop object of the store front.
     * The shop is used to build links or to select the
     * resource translations.
     *
     * @var Shop
     */
    private $shop;

    /**
     * @var Area
     */
    private $area;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var State
     */
    private $state;

    /**
     * @param \Shopware\Struct\Currency $currency
     *
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

    }

    /**
     * @return \Shopware\Struct\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param \Shopware\Struct\CustomerGroup $currentCustomerGroup
     * @return $this
     */
    public function setCurrentCustomerGroup($currentCustomerGroup)
    {
        $this->currentCustomerGroup = $currentCustomerGroup;
        return $this;
    }

    /**
     * @return \Shopware\Struct\CustomerGroup
     */
    public function getCurrentCustomerGroup()
    {
        return $this->currentCustomerGroup;
    }

    /**
     * @param \Shopware\Struct\CustomerGroup $fallbackCustomerGroup
     * @return $this
     */
    public function setFallbackCustomerGroup($fallbackCustomerGroup)
    {
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;
        return $this;
    }

    /**
     * @return \Shopware\Struct\CustomerGroup
     */
    public function getFallbackCustomerGroup()
    {
        return $this->fallbackCustomerGroup;
    }

    /**
     * @return \Shopware\Struct\Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param \Shopware\Struct\Shop $shop
     * @return $this
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
        return $this;
    }

    /**
     * @return \Shopware\Struct\Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param \Shopware\Struct\Area $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return \Shopware\Struct\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param \Shopware\Struct\Country $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return \Shopware\Struct\State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param \Shopware\Struct\State $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return \Shopware\Struct\Tax[]
     */
    public function getTaxRules()
    {
        return $this->taxRules;
    }

    /**
     * @param \Shopware\Struct\Tax[] $taxRules
     */
    public function setTaxRules($taxRules)
    {
        $this->taxRules = $taxRules;
    }

    public function getTaxRule($taxId)
    {
        $key = 'tax_' . $taxId;
        return $this->taxRules[$key];
    }

}