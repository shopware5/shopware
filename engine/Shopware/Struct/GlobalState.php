<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class GlobalState
{
    /**
     * @var Tax
     */
    private $tax;

    /**
     * @var CustomerGroup
     */
    private $currentCustomerGroup;

    /**
     * @var CustomerGroup
     */
    private $fallbackCustomerGroup;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @param \Shopware\Struct\Tax $tax
     *
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

    }

    /**
     * @return \Shopware\Struct\Tax
     */
    public function getTax()
    {
        return $this->tax;
    }

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
}