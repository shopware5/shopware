<?php

namespace Shopware\Service\Core;

use Shopware\Components\DependencyInjection\Container;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;
use Shopware\Models;

/**
 * @package Shopware\Service
 */
class GlobalState implements Service\GlobalState
{
    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    private $container;

    /**
     * @var CustomerGroup
     */
    private $customerGroupGateway;

    /**
     * @var Tax
     */
    private $taxGateway;

    /**
     * @var Struct\Context
     */
    private $context = null;

    /**
     * @param Container $container
     * @param Gateway\CustomerGroup $customerGroupGateway
     * @param Gateway\Tax $taxGateway
     * @param Gateway\Country $countryGateway
     */
    function __construct(
        Container $container,
        Gateway\CustomerGroup $customerGroupGateway,
        Gateway\Tax $taxGateway,
        Gateway\Country $countryGateway
    ) {
        $this->container = $container;
        $this->taxGateway = $taxGateway;
        $this->countryGateway = $countryGateway;
        $this->customerGroupGateway = $customerGroupGateway;
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        $session = $this->container->get('session');

        /**@var $shop Shop */
        $shop = $this->container->get('shop');

        $fallback = $shop->getCustomerGroup()->getKey();

        if ($session->offsetExists('sUserGroup') && $session->offsetGet('sUserGroup')) {
            $key = $session->offsetGet('sUserGroup');
        } else {
            $key = $fallback;
        }

        $context = new Struct\Context();

        $context->setShop(
            $this->createShopStruct($shop)
        );

        $context->setCurrency(
            $this->createCurrencyStruct($shop->getCurrency())
        );

        $context->setCurrentCustomerGroup(
            $this->customerGroupGateway->get($key)
        );

        $context->setFallbackCustomerGroup(
            $this->customerGroupGateway->get($fallback)
        );

        $area = null;
        if ($session->offsetGet('sArea')) {
            $area = $this->countryGateway->getArea(
                $session->offsetGet('sArea'),
                $context
            );
        }

        $country = null;
        if ($session->offsetGet('sCountry')) {
            $country = $this->countryGateway->getCountry(
                $session->offsetGet('sCountry'),
                $context
            );
        }

        $state = null;
        if ($session->offsetGet('sState')) {
            $state = $this->countryGateway->getState(
                $session->offsetGet('sState'),
                $context
            );
        }

        $rules = $this->taxGateway->getRules(
            $context->getCurrentCustomerGroup(),
            $area,
            $country,
            $state
        );

        $context->setArea($area);

        $context->setCountry($country);

        $context->setState($state);

        $context->setTaxRules($rules);

        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        if (!$this->context) {
            $this->initialize();
        }

        return $this->context;
    }

    /**
     * Converts a currency doctrine model to a currency struct
     *
     * @param Models\Shop\Currency $currency
     * @return Struct\Currency
     */
    private function createCurrencyStruct(Models\Shop\Currency $currency)
    {
        $struct = new Struct\Currency();

        $struct->setId($currency->getId());
        $struct->setName($currency->getName());
        $struct->setCurrency($currency->getCurrency());
        $struct->setFactor($currency->getFactor());
        $struct->setSymbol($currency->getSymbol());

        return $struct;
    }

    /**
     * Converts a shop doctrine model to a shop struct
     * @param Models\Shop\Shop $shop
     * @return Struct\Shop
     */
    private function createShopStruct(Models\Shop\Shop $shop)
    {
        $struct = new Struct\Shop();
        $struct->setId($shop->getId());

        $struct->setName($shop->getName());
        $struct->setHost($shop->getHost());
        $struct->setPath($shop->getBasePath());
        $struct->setUrl($shop->getBaseUrl());
        $struct->setSecure($shop->getSecure());
        $struct->setSecureHost($shop->getSecureHost());
        $struct->setSecurePath($struct->getSecurePath());

        if ($shop->getCategory()) {
            $struct->setCategory(
                $this->convertCategoryStruct($shop->getCategory())
            );
        }

        return $struct;
    }

    /**
     * @param Models\Category\Category $category
     * @return Struct\Category
     */
    private function convertCategoryStruct(Models\Category\Category $category)
    {
        $struct = new Struct\Category();

        $struct->setId($category->getId());
        $struct->setName($category->getName());
        $struct->setPath($category->getPath());

        return $struct;
    }
}
