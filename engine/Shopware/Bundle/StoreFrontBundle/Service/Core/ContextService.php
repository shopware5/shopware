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
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContextService implements ContextServiceInterface
{
    const FALLBACK_CUSTOMER_GROUP = 'EK';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ShopContextInterface
     */
    private $context = null;

    /**
     * @var ShopContextFactoryInterface
     */
    private $shopContextFactory;

    public function __construct(
        ContainerInterface $container,
        ShopContextFactoryInterface $shopContextFactory
    ) {
        $this->container = $container;
        $this->shopContextFactory = $shopContextFactory;
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
        $this->context = $this->shopContextFactory->create(
            $this->getStoreFrontBaseUrl(),
            $this->getStoreFrontShopId(),
            $this->getStoreFrontCurrencyId(),
            $this->getStoreFrontCurrentCustomerGroupKey(),
            $this->getStoreFrontAreaId(),
            $this->getStoreFrontCountryId(),
            $this->getStoreFrontStateId(),
            $this->getStoreFrontStreamIds()
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
        return $this->shopContextFactory->create(
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
        return $this->shopContextFactory->create(
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
        /** @var \Shopware_Components_Config $config */
        $config = $this->container->get('config');

        $request = null;
        if ($this->container->initialized('front')) {
            /** @var \Enlight_Controller_Front $front */
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
        /** @var Models\Shop\Shop $shop */
        $shop = $this->container->get('shop');

        return $shop->getId();
    }

    /**
     * @return int
     */
    private function getStoreFrontCurrencyId()
    {
        /** @var Models\Shop\Shop $shop */
        $shop = $this->container->get('shop');

        return $shop->getCurrency()->getId();
    }

    /**
     * @return string
     */
    private function getStoreFrontCurrentCustomerGroupKey()
    {
        /** @var Session $session */
        $session = $this->container->get('session');
        if ($session->offsetExists('sUserGroup') && $session->offsetGet('sUserGroup')) {
            return $session->offsetGet('sUserGroup');
        }

        /** @var Models\Shop\Shop $shop */
        $shop = $this->container->get('shop');

        return $shop->getCustomerGroup()->getKey();
    }

    /**
     * @return int|null
     */
    private function getStoreFrontAreaId()
    {
        /** @var Session $session */
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
        /** @var Session $session */
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
        /** @var Session $session */
        $session = $this->container->get('session');
        if ($session->offsetGet('sState')) {
            return $session->offsetGet('sState');
        }

        return null;
    }

    /**
     * @param int $customerId
     *
     * @return string[]
     */
    private function getStreamsOfCustomerId($customerId)
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select('mapping.stream_id');
        $query->from('s_customer_streams_mapping', 'mapping');
        $query->where('mapping.customer_id = :customerId');
        $query->setParameter(':customerId', $customerId);
        $streams = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
        sort($streams);

        return $streams;
    }

    /**
     * @return array
     */
    private function getStoreFrontStreamIds()
    {
        $customerId = $this->getStoreFrontCustomerId();

        if (!$customerId) {
            return [];
        }

        return $this->getStreamsOfCustomerId($customerId);
    }

    /**
     * @return int|null
     */
    private function getStoreFrontCustomerId()
    {
        $session = $this->container->get('session');
        if ($session->offsetGet('sUserId')) {
            return (int) $session->offsetGet('sUserId');
        }

        if ($session->offsetGet('auto-user')) {
            return (int) $session->offsetGet('auto-user');
        }

        return null;
    }
}
