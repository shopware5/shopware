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
namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Enlight_Components_Session_Namespace as Session;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Models;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Service\Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ContextService implements Service\ContextServiceInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Gateway\CustomerGroupGatewayInterface
     */
    private $customerGroupGateway;

    /**
     * @var Gateway\TaxGatewayInterface
     */
    private $taxGateway;

    /**
     * @var Struct\Context
     */
    private $context = null;

    /**
     * @var Struct\ProductContext
     */
    private $productContext = null;

    /**
     * @var Gateway\PriceGroupDiscountGatewayInterface
     */
    private $priceGroupDiscountGateway;

    /**
     * @param Container $container
     * @param Gateway\CustomerGroupGatewayInterface $customerGroupGateway
     * @param Gateway\TaxGatewayInterface $taxGateway
     * @param Gateway\CountryGatewayInterface $countryGateway
     * @param Gateway\PriceGroupDiscountGatewayInterface $priceGroupDiscountGateway
     */
    public function __construct(
        Container $container,
        Gateway\CustomerGroupGatewayInterface $customerGroupGateway,
        Gateway\TaxGatewayInterface $taxGateway,
        Gateway\CountryGatewayInterface $countryGateway,
        Gateway\PriceGroupDiscountGatewayInterface $priceGroupDiscountGateway
    ) {
        $this->container = $container;
        $this->taxGateway = $taxGateway;
        $this->countryGateway = $countryGateway;
        $this->customerGroupGateway = $customerGroupGateway;
        $this->priceGroupDiscountGateway = $priceGroupDiscountGateway;
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
     * @inheritdoc
     */
    public function getProductContext()
    {
        if (!$this->productContext) {
            $this->initializeProductContext();
        }
        return $this->productContext;
    }

    /**
     * @inheritdoc
     */
    private function initializeProductContext()
    {
        if (!$this->context) {
            $this->initialize();
        }

        $this->productContext = Struct\ProductContext::createFromContext($this->context);

        /** @var $session Session */
        $session = $this->container->get('session');

        $area    = $this->createAreaStruct($session, $this->productContext);
        $country = $this->createCountryStruct($session, $this->productContext);
        $state   = $this->createStateStruct($session, $this->productContext);
        $rules   = $this->createTaxRulesStruct($this->productContext, $area, $country, $state);

        $this->productContext->setArea($area);
        $this->productContext->setCountry($country);
        $this->productContext->setState($state);
        $this->productContext->setTaxRules($rules);

        $priceGroups = $this->priceGroupDiscountGateway->getPriceGroups(
            $this->productContext->getCurrentCustomerGroup(),
            $this->productContext
        );

        $this->productContext->setPriceGroups($priceGroups);
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        /** @var $session Session */
        $session = $this->container->get('session');

        /**@var $shop Models\Shop\Shop */
        $shop = $this->container->get('shop');

        $fallback = $shop->getCustomerGroup()->getKey();

        if ($session->offsetExists('sUserGroup') && $session->offsetGet('sUserGroup')) {
            $key = $session->offsetGet('sUserGroup');
        } else {
            $key = $fallback;
        }

        $context = new Struct\Context();

        $context->setBaseUrl(
            $this->buildBaseUrl()
        );

        $context->setShop(
            Struct\Shop::createFromShopEntity($shop)
        );

        $context->setCurrency(
            Struct\Currency::createFromCurrencyEntity($shop->getCurrency())
        );

        $context->setCurrentCustomerGroup(
            $this->customerGroupGateway->get($key)
        );

        $context->setFallbackCustomerGroup(
            $this->customerGroupGateway->get($fallback)
        );

        $this->context = $context;
    }


    /**
     * @return string
     */
    private function buildBaseUrl()
    {
        /** @var $config \Shopware_Components_Config */
        $config = $this->container->get('config');

        $request = null;
        if ($this->container->initialized('front')) {
            /** @var $front \Enlight_Controller_Front */
            $front = $this->container->get('front');
            $request = $front->Request();
        }

        if ($request !== null) {
            $baseUrl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        } else {
            $baseUrl = 'http://' . $config->get('basePath');
        }

        return $baseUrl;
    }

    /**
     * @param Session $session
     * @param Struct\Context $context
     * @return null|Struct\Country\Area
     */
    protected function createAreaStruct(Session $session, Struct\Context $context)
    {
        $area = null;
        if ($session->offsetGet('sArea')) {
            $area = $this->countryGateway->getArea(
                $session->offsetGet('sArea'),
                $context
            );

            return $area;
        }

        return $area;
    }

    /**
     * @param Session $session
     * @param Struct\Context $context
     * @return null|Struct\Country
     */
    protected function createCountryStruct(Session $session, Struct\Context $context)
    {
        $country = null;
        if ($session->offsetGet('sCountry')) {
            $country = $this->countryGateway->getCountry(
                $session->offsetGet('sCountry'),
                $context
            );

            return $country;
        }

        return $country;
    }

    /**
     * @param Session $session
     * @param Struct\Context $context
     * @return null|Struct\Country\State
     */
    protected function createStateStruct(Session $session, Struct\Context $context)
    {
        $state = null;
        if ($session->offsetGet('sState')) {
            $state = $this->countryGateway->getState(
                $session->offsetGet('sState'),
                $context
            );

            return $state;
        }

        return $state;
    }

    /**
     * @param Struct\Context $context
     * @param Struct\Country\Area $area
     * @param Struct\Country $country
     * @param Struct\Country\State $state
     * @return Struct\Tax[]
     */
    protected function createTaxRulesStruct(
        Struct\Context $context,
        Struct\Country\Area $area,
        Struct\Country $country,
        Struct\Country\State $state
    ) {
        $rules = $this->taxGateway->getRules(
            $context->getCurrentCustomerGroup(),
            $area,
            $country,
            $state
        );

        return $rules;
    }
}
