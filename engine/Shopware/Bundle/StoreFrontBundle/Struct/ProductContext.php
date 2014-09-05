<?php

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;

class ProductContext extends Context
{
    /**
     * @var Tax[]
     */
    protected $taxRules;

    /**
     * @var PriceGroup[]
     */
    protected $priceGroups;

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
     * @param Context $context
     * @return ProductContext
     */
    public static function createFromContext(Context $context)
    {
        $self = new self();

        $self->setBaseUrl($context->getBaseUrl());

        $self->setFallbackCustomerGroup($context->getFallbackCustomerGroup());

        $self->setCurrentCustomerGroup($context->getCurrentCustomerGroup());

        $self->setShop($context->getShop());

        $self->setCurrency($context->getCurrency());

        $self->addAttributes($context->getAttributes());

        return $self;
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
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup[]
     */
    public function getPriceGroups()
    {
        return $this->priceGroups;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup[] $priceGroups
     */
    public function setPriceGroups($priceGroups)
    {
        $this->priceGroups = $priceGroups;
    }
}