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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Enlight_Components_Session_Namespace as Session;
use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\CurrencyGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomerGroupGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\PriceGroupDiscountGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\TaxGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ContextService implements ContextServiceInterface
{
    const FALLBACK_CUSTOMER_GROUP = 'EK';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ProductContextInterface
     */
    private $context = null;

    /**
     * @var CustomerGroupGatewayInterface
     */
    private $customerGroupGateway;

    /**
     * @var TaxGatewayInterface
     */
    private $taxGateway;

    /**
     * @var PriceGroupDiscountGatewayInterface
     */
    private $priceGroupDiscountGateway;

    /**
     * @var ShopGatewayInterface
     */
    private $shopGateway;

    /**
     * @var CurrencyGatewayInterface
     */
    private $currencyGateway;

    /**
     * @var CountryGatewayInterface
     */
    private $countryGateway;

    /**
     * @param Container                          $container
     * @param CustomerGroupGatewayInterface      $customerGroupGateway
     * @param TaxGatewayInterface                $taxGateway
     * @param CountryGatewayInterface            $countryGateway
     * @param PriceGroupDiscountGatewayInterface $priceGroupDiscountGateway
     * @param ShopGatewayInterface               $shopGateway
     * @param CurrencyGatewayInterface           $currencyGateway
     */
    public function __construct(
        Container $container,
        CustomerGroupGatewayInterface $customerGroupGateway,
        TaxGatewayInterface $taxGateway,
        CountryGatewayInterface $countryGateway,
        PriceGroupDiscountGatewayInterface $priceGroupDiscountGateway,
        ShopGatewayInterface $shopGateway,
        CurrencyGatewayInterface $currencyGateway
    ) {
        $this->container = $container;
        $this->taxGateway = $taxGateway;
        $this->countryGateway = $countryGateway;
        $this->customerGroupGateway = $customerGroupGateway;
        $this->priceGroupDiscountGateway = $priceGroupDiscountGateway;
        $this->shopGateway = $shopGateway;
        $this->currencyGateway = $currencyGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getShopContext()
    {
        if ($this->context === null) {
            $this->initializeShopContext();
        }

        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeShopContext()
    {
        $this->context = $this->create(
            $this->getStoreFrontBaseUrl(),
            $this->getStoreFrontShopId(),
            $this->getStoreFrontCurrencyId(),
            $this->getStoreFrontCurrentCustomerGroupKey(),
            $this->getStoreFrontAreaId(),
            $this->getStoreFrontCountryId(),
            $this->getStoreFrontStateId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->getShopContext();
    }

    /**
     * {@inheritdoc}
     */
    public function getProductContext()
    {
        return $this->getShopContext();
    }

    /**
     * {@inheritdoc}
     */
    public function getLocationContext()
    {
        return $this->getShopContext();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeContext()
    {
        $this->initializeShopContext();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeLocationContext()
    {
        $this->initializeShopContext();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeProductContext()
    {
        $this->initializeShopContext();
    }

    /**
     * {@inheritdoc}
     */
    public function createProductContext($shopId, $currencyId = null, $customerGroupKey = null)
    {
        return $this->create(
            $this->getStoreFrontBaseUrl(),
            $shopId,
            $currencyId,
            $customerGroupKey
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createShopContext($shopId, $currencyId = null, $customerGroupKey = null)
    {
        return $this->create(
            $this->getStoreFrontBaseUrl(),
            $shopId,
            $currencyId,
            $customerGroupKey
        );
    }

    /**
     * @return string
     */
    private function getStoreFrontBaseUrl()
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
            return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        }

        return 'http://' . $config->get('basePath');
    }

    /**
     * @return int
     */
    private function getStoreFrontShopId()
    {
        /** @var $shop Models\Shop\Shop */
        $shop = $this->container->get('shop');

        return $shop->getId();
    }

    /**
     * @return int
     */
    private function getStoreFrontCurrencyId()
    {
        /** @var $shop Models\Shop\Shop */
        $shop = $this->container->get('shop');

        return $shop->getCurrency()->getId();
    }

    /**
     * @return string
     */
    private function getStoreFrontCurrentCustomerGroupKey()
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($session->offsetExists('sUserGroup') && $session->offsetGet('sUserGroup')) {
            return $session->offsetGet('sUserGroup');
        }

        /** @var $shop Models\Shop\Shop */
        $shop = $this->container->get('shop');

        return $shop->getCustomerGroup()->getKey();
    }

    /**
     * @return int|null
     */
    private function getStoreFrontAreaId()
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($session->offsetGet('sArea')) {
            return $session->offsetGet('sArea');
        }

        return null;
    }

    /**
     * @return int|null
     */
    private function getStoreFrontCountryId()
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($session->offsetGet('sCountry')) {
            return $session->offsetGet('sCountry');
        }

        return null;
    }

    /**
     * @return int|null
     */
    private function getStoreFrontStateId()
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($session->offsetGet('sState')) {
            return $session->offsetGet('sState');
        }

        return null;
    }

    /**
     * @param string      $baseUrl
     * @param int         $shopId
     * @param null|int    $currencyId
     * @param null|string $currentCustomerGroupKey
     * @param null|int    $areaId
     * @param null|int    $countryId
     * @param null|int    $stateId
     *
     * @return ShopContext
     */
    private function create(
        $baseUrl,
        $shopId,
        $currencyId = null,
        $currentCustomerGroupKey = null,
        $areaId = null,
        $countryId = null,
        $stateId = null
    ) {
        $shop = $this->shopGateway->get($shopId);
        $fallbackCustomerGroupKey = self::FALLBACK_CUSTOMER_GROUP;

        if ($currentCustomerGroupKey == null) {
            $currentCustomerGroupKey = $fallbackCustomerGroupKey;
        }

        $groups = $this->customerGroupGateway->getList([$currentCustomerGroupKey, $fallbackCustomerGroupKey]);

        $currentCustomerGroup = $groups[$currentCustomerGroupKey];
        $fallbackCustomerGroup = $groups[$fallbackCustomerGroupKey];

        $currency = null;
        if ($currencyId != null) {
            $currency = $this->currencyGateway->getList([$currencyId]);
            $currency = array_shift($currency);
        }
        if (!$currency) {
            $currency = $shop->getCurrency();
        }

        $context = new ShopContext($baseUrl, $shop, $currency, $currentCustomerGroup, $fallbackCustomerGroup, [], [], null, null, null);

        $area = null;
        if ($areaId !== null) {
            $area = $this->countryGateway->getArea($areaId, $context);
        }

        $country = null;
        if ($countryId !== null) {
            $country = $this->countryGateway->getCountry($countryId, $context);
        }

        $state = null;
        if ($stateId !== null) {
            $state = $this->countryGateway->getState($stateId, $context);
        }

        $taxRules = $this->taxGateway->getRules($currentCustomerGroup, $area, $country, $state);
        $priceGroups = $this->priceGroupDiscountGateway->getPriceGroups($currentCustomerGroup, $context);

        return new ShopContext(
            $baseUrl,
            $shop,
            $currency,
            $currentCustomerGroup,
            $fallbackCustomerGroup,
            $taxRules,
            $priceGroups,
            $area,
            $country,
            $state
        );
    }
}
