<?php

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class TestContext extends ShopContext
{
    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Country\Area $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Country $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Currency $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group $currentCustomerGroup
     */
    public function setCurrentCustomerGroup($currentCustomerGroup)
    {
        $this->currentCustomerGroup = $currentCustomerGroup;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group $fallbackCustomerGroup
     */
    public function setFallbackCustomerGroup($fallbackCustomerGroup)
    {
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup[] $priceGroups
     */
    public function setPriceGroups($priceGroups)
    {
        $this->priceGroups = $priceGroups;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Country\State $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Tax[] $taxRules
     */
    public function setTaxRules($taxRules)
    {
        $this->taxRules = $taxRules;
    }
}
