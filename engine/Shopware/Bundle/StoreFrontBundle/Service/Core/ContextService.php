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
    const FALLBACK_CUSTOMER_GROUP = "EK";

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
     * @var Struct\LocationContext
     */
    private $locationContext = null;

    /**
     * @var Struct\ProductContext
     */
    private $productContext = null;

    /**
     * @var Struct\ShopContext
     */
    private $shopContext = null;

    /**
     * @var Gateway\PriceGroupDiscountGatewayInterface
     */
    private $priceGroupDiscountGateway;

    /**
     * @var Gateway\ShopGatewayInterface
     */
    private $shopGateway;

    /**
     * @var Gateway\CurrencyGatewayInterface
     */
    private $currencyGateway;

    /**
     * @param Container $container
     * @param Gateway\CustomerGroupGatewayInterface $customerGroupGateway
     * @param Gateway\TaxGatewayInterface $taxGateway
     * @param Gateway\CountryGatewayInterface $countryGateway
     * @param Gateway\PriceGroupDiscountGatewayInterface $priceGroupDiscountGateway
     * @param Gateway\ShopGatewayInterface $shopGateway
     * @param Gateway\CurrencyGatewayInterface $currencyGateway
     */
    public function __construct(
        Container $container,
        Gateway\CustomerGroupGatewayInterface $customerGroupGateway,
        Gateway\TaxGatewayInterface $taxGateway,
        Gateway\CountryGatewayInterface $countryGateway,
        Gateway\PriceGroupDiscountGatewayInterface $priceGroupDiscountGateway,
        Gateway\ShopGatewayInterface $shopGateway,
        Gateway\CurrencyGatewayInterface $currencyGateway
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
     * @inheritdoc
     */
    public function getContext()
    {
        if (!$this->context) {
            $this->initializeContext();
        }

        return $this->context;
    }

    /**
     * @inheritdoc
     */
    public function getShopContext()
    {
        if (!$this->shopContext) {
            $this->initializeShopContext();
        }

        return $this->shopContext;
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
     * @return Struct\LocationContext
     */
    public function getLocationContext()
    {
        if (!$this->locationContext) {
            $this->initializeLocationContext();
        }
        return $this->locationContext;
    }

    /**
     * @inheritdoc
     */
    public function initializeContext()
    {
        $locationContext = $this->getLocationContext();

        $productContext = $this->getProductContext();

        $this->context = Struct\Context::createFromContexts(
            $productContext,
            $locationContext
        );
    }

    /**
     * Initials the shop context which contains
     * all information about the current shop state.
     */
    public function initializeShopContext()
    {
        /** @var $session Session */
        $session = $this->container->get('session');

        /**@var $shop Models\Shop\Shop */
        $shop = $this->container->get('shop');

        if ($session->offsetExists('sUserGroup') && $session->offsetGet('sUserGroup')) {
            $key = $session->offsetGet('sUserGroup');
        } else {
            $key = $shop->getCustomerGroup()->getKey();
        }

        $this->shopContext = $this->createShopContext(
            $shop->getId(),
            $shop->getCurrency()->getId(),
            $key
        );
    }

    /**
     * Initials the location context which contains
     * the information about the current country state.
     */
    public function initializeLocationContext()
    {
        $shopContext = $this->getShopContext();

        /** @var $session Session */
        $session = $this->container->get('session');

        $area    = $this->createAreaStruct($session, $shopContext);
        $country = $this->createCountryStruct($session, $shopContext);
        $state   = $this->createStateStruct($session, $shopContext);

        $this->locationContext = new Struct\LocationContext(
            $area,
            $country,
            $state
        );
    }

    /**
     * Initials the product context which contains
     * the information about the tax rules and price group discounts.
     */
    public function initializeProductContext()
    {
        $shopContext = $this->getShopContext();

        $locationContext = $this->getLocationContext();

        $rules = $this->createTaxRulesStruct(
            $shopContext,
            $locationContext->getArea(),
            $locationContext->getCountry(),
            $locationContext->getState()
        );

        $priceGroups = $this->priceGroupDiscountGateway->getPriceGroups(
            $shopContext->getCurrentCustomerGroup(),
            $shopContext
        );
        $this->productContext = Struct\ProductContext::createFromContexts(
            $shopContext,
            $rules,
            $priceGroups
        );
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
     * @param Struct\ShopContextInterface $context
     * @return null|Struct\Country\Area
     */
    protected function createAreaStruct(Session $session, Struct\ShopContextInterface $context)
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
     * @param Struct\ShopContextInterface $context
     * @return null|Struct\Country
     */
    protected function createCountryStruct(Session $session, Struct\ShopContextInterface $context)
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
     * @param Struct\ShopContextInterface $context
     * @return null|Struct\Country\State
     */
    protected function createStateStruct(Session $session, Struct\ShopContextInterface $context)
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
     * @param Struct\ShopContextInterface $context
     * @param Struct\Country\Area|null $area
     * @param Struct\Country|null $country
     * @param Struct\Country\State|null $state
     * @return Struct\Tax[]
     */
    protected function createTaxRulesStruct(
        Struct\ShopContextInterface $context,
        Struct\Country\Area $area = null,
        Struct\Country $country = null,
        Struct\Country\State $state = null
    ) {
        $rules = $this->taxGateway->getRules(
            $context->getCurrentCustomerGroup(),
            $area,
            $country,
            $state
        );

        return $rules;
    }

    /**
     * @param int $shopId
     * @param null|int $currencyId
     * @param null|int $customerGroupKey
     * @return Struct\ProductContext
     */
    public function createProductContext($shopId, $currencyId = null, $customerGroupKey = null)
    {
        $shopContext = $this->createShopContext($shopId, $currencyId, $customerGroupKey);

        $locationContext = new Struct\LocationContext(null, null, null, null);

        $rules = $this->createTaxRulesStruct(
            $shopContext,
            $locationContext->getArea(),
            $locationContext->getCountry(),
            $locationContext->getState()
        );

        $priceGroups = $this->priceGroupDiscountGateway->getPriceGroups(
            $shopContext->getCurrentCustomerGroup(),
            $shopContext
        );

        return Struct\ProductContext::createFromContexts(
            $shopContext,
            $rules,
            $priceGroups
        );
    }

    /**
     * @param int $shopId
     * @param null|int $currencyId
     * @param null|int $customerGroupKey
     * @return Struct\ShopContext
     */
    public function createShopContext($shopId, $currencyId = null, $customerGroupKey = null)
    {
        $shop = $this->shopGateway->get($shopId);
        $fallback = self::FALLBACK_CUSTOMER_GROUP;

        if ($customerGroupKey == null) {
            $customerGroupKey = $fallback;
        }

        $groups = $this->customerGroupGateway->getList([$customerGroupKey, $fallback]);

        $currency = null;
        if ($currencyId != null) {
            $currency = $this->currencyGateway->getList([$currencyId]);
            $currency = array_shift($currency);
        }
        if (!$currency) {
            $currency = $shop->getCurrency();
        }

        return new Struct\ShopContext(
            $this->buildBaseUrl(),
            $shop,
            $currency,
            $groups[$customerGroupKey],
            $groups[$fallback]
        );
    }
}
