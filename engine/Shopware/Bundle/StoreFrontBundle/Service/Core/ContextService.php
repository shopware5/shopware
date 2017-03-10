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
use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\CurrencyGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomerGroupGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\PriceGroupDiscountGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\TaxGateway;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
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
     * @var ShopContextInterface
     */
    private $context = null;

    /**
     * @var CustomerGroupGateway
     */
    private $customerGroupGateway;

    /**
     * @var TaxGateway
     */
    private $taxGateway;

    /**
     * @var PriceGroupDiscountGateway
     */
    private $priceGroupDiscountGateway;

    /**
     * @var ShopGateway
     */
    private $shopGateway;

    /**
     * @var CurrencyGateway
     */
    private $currencyGateway;

    /**
     * @var CountryGateway
     */
    private $countryGateway;

    /**
     * @param Container                 $container
     * @param CustomerGroupGateway      $customerGroupGateway
     * @param TaxGateway                $taxGateway
     * @param CountryGateway            $countryGateway
     * @param PriceGroupDiscountGateway $priceGroupDiscountGateway
     * @param ShopGateway               $shopGateway
     * @param CurrencyGateway           $currencyGateway
     */
    public function __construct(
        Container $container,
        CustomerGroupGateway $customerGroupGateway,
        TaxGateway $taxGateway,
        CountryGateway $countryGateway,
        PriceGroupDiscountGateway $priceGroupDiscountGateway,
        ShopGateway $shopGateway,
        CurrencyGateway $currencyGateway
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
        $shop = $this->shopGateway->getList([$shopId]);
        $shop = array_shift($shop);

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
            $area = $this->countryGateway->getAreas([$areaId], $context->getTranslationContext());
            $area = array_shift($area);
        }

        $country = null;
        if ($countryId !== null) {
            $country = $this->countryGateway->getCountries([$countryId], $context->getTranslationContext());
            $country = array_shift($country);
        }

        $state = null;
        if ($stateId !== null) {
            $state = $this->countryGateway->getStates([$stateId], $context->getTranslationContext());
            $state = array_shift($state);
        }

        $taxRules = $this->taxGateway->getRules($currentCustomerGroup, $area, $country, $state);
        $priceGroups = $this->priceGroupDiscountGateway->getPriceGroups($currentCustomerGroup);

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
