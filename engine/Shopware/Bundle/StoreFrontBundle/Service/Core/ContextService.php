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
use Shopware\Bundle\StoreFrontBundle\Service\CacheInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\CheckoutDefinition;
use Shopware\Bundle\StoreFrontBundle\Struct\CustomerDefinition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopDefinition;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var ContextFactoryInterface
     */
    private $factory;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ShopContextInterface
     */
    private $context = null;

    public function __construct(ContainerInterface $container, ContextFactoryInterface $factory, CacheInterface $cache)
    {
        $this->container = $container;
        $this->factory = $factory;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getShopContext()
    {
        if ($this->context === null) {
            $this->context = $this->load();
        }

        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeShopContext()
    {
        $this->context = $this->load(false);
    }

    private function load($cached = true): ShopContextInterface
    {
        $shopDefinition = new ShopDefinition(
            $this->getStoreFrontShopId(),
            $this->getStoreFrontCurrencyId()
        );

        $customerDefinition = new CustomerDefinition(
            $this->getStoreCustomerId(),
            $this->getStoreFrontBillingAddressId(),
            $this->getStoreFrontShippingAddressId()
        );

        $checkoutDefinition = new CheckoutDefinition(
            $this->getStoreFrontPaymentId(),
            $this->getStoreFrontDispatchId(),
            $this->getStoreFrontCountryId(),
            $this->getStoreFrontStateId()
        );

        $key = $this->getCacheKey($shopDefinition, $customerDefinition, $checkoutDefinition);

        if ($cached && $context = $this->cache->fetch($key)) {
            return unserialize($context);
        }

        $context = $this->factory->create($shopDefinition, $customerDefinition, $checkoutDefinition);

        $this->write($context);

        $this->cache->save($key, serialize($context), 3600);

        return $context;
    }

    private function write(ShopContextInterface $context)
    {
        $key = $this->getCacheKey(
            ShopDefinition::createFromContext($context),
            CustomerDefinition::createFromContext($context),
            CheckoutDefinition::createFromContext($context)
        );
        $this->cache->save($key, serialize($context), 3600);
    }

    private function getCacheKey(
        ShopDefinition $shopDefinition,
        CustomerDefinition $customerDefinition,
        CheckoutDefinition $checkoutDefinition
    ): string {
        return md5(
            json_encode($shopDefinition) .
            json_encode($customerDefinition) .
            json_encode($checkoutDefinition)
        );
    }

    /**
     * @return int
     */
    private function getStoreFrontShopId(): int
    {
        /** @var $shop Models\Shop\Shop */
        $shop = $this->container->get('shop');

        return (int) $shop->getId();
    }

    /**
     * @return int
     */
    private function getStoreFrontCurrencyId(): int
    {
        /** @var $shop Models\Shop\Shop */
        $shop = $this->container->get('shop');

        return (int) $shop->getCurrency()->getId();
    }

    /**
     * @return int|null
     */
    private function getStoreFrontCountryId(): ?int
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($countryId = $session->offsetGet('sCountry')) {
            return (int) $countryId;
        }

        return null;
    }

    /**
     * @return int|null
     */
    private function getStoreFrontStateId(): ?int
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($stateId = $session->offsetGet('sState')) {
            return (int) $stateId;
        }

        return null;
    }

    private function getStoreCustomerId(): ?int
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($customerId = $session->offsetGet('sUserId')) {
            return (int) $customerId;
        }

        return null;
    }

    private function getStoreFrontBillingAddressId(): ?int
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($addressId = $session->offsetGet('checkoutBillingAddressId')) {
            return (int) $addressId;
        }

        return null;
    }

    private function getStoreFrontShippingAddressId(): ?int
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($addressId = $session->offsetGet('checkoutShippingAddressId')) {
            return (int) $addressId;
        }

        return null;
    }

    private function getStoreFrontPaymentId(): ?int
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($paymentId = $session->offsetGet('sPayment')) {
            return (int) $paymentId;
        }

        return null;
    }

    private function getStoreFrontDispatchId(): ?int
    {
        /** @var $session Session */
        $session = $this->container->get('session');
        if ($dispatchId = $session->offsetGet('sDispatch')) {
            return (int) $dispatchId;
        }

        return null;
    }
}
