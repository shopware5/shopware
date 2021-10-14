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

use Doctrine\DBAL\Connection;
use PDO;
use RuntimeException;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContextService implements ContextServiceInterface
{
    public const FALLBACK_CUSTOMER_GROUP = 'EK';

    private ContainerInterface $container;

    private ?ShopContextInterface $context = null;

    private ShopContextFactoryInterface $shopContextFactory;

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

        if ($this->context === null) {
            throw new RuntimeException('Shop context not initialized correctly');
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

    private function getStoreFrontBaseUrl(): string
    {
        $config = $this->container->get(Shopware_Components_Config::class);

        $request = null;
        if ($this->container->initialized('front')) {
            $request = $this->container->get('front')->Request();
        }

        if ($request !== null) {
            return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        }

        return 'http://' . $config->get('basePath');
    }

    private function getStoreFrontShopId(): int
    {
        return $this->getShop()->getId();
    }

    private function getStoreFrontCurrencyId(): int
    {
        return $this->getShop()->getCurrency()->getId();
    }

    private function getStoreFrontCurrentCustomerGroupKey(): string
    {
        $session = $this->container->get('session');
        if ($session->offsetExists('sUserGroup') && $session->offsetGet('sUserGroup')) {
            return $session->offsetGet('sUserGroup');
        }

        return $this->getShop()->getCustomerGroup()->getKey();
    }

    private function getStoreFrontAreaId(): ?int
    {
        $session = $this->container->get('session');
        if ($session->offsetGet('sArea')) {
            return $session->offsetGet('sArea');
        }

        return null;
    }

    private function getStoreFrontCountryId(): ?int
    {
        $session = $this->container->get('session');
        if ($session->offsetGet('sCountry')) {
            return $session->offsetGet('sCountry');
        }

        return null;
    }

    private function getStoreFrontStateId(): ?int
    {
        $session = $this->container->get('session');
        if ($session->offsetGet('sState')) {
            return $session->offsetGet('sState');
        }

        return null;
    }

    /**
     * @return int[]
     */
    private function getStreamsOfCustomerId(int $customerId): array
    {
        $query = $this->container->get(Connection::class)->createQueryBuilder();
        $query->select('mapping.stream_id');
        $query->from('s_customer_streams_mapping', 'mapping');
        $query->where('mapping.customer_id = :customerId');
        $query->setParameter(':customerId', $customerId);
        $streams = $query->execute()->fetchAll(PDO::FETCH_COLUMN);
        sort($streams);

        return $streams;
    }

    /**
     * @return int[]
     */
    private function getStoreFrontStreamIds(): array
    {
        $customerId = $this->getStoreFrontCustomerId();

        if ($customerId === null) {
            return [];
        }

        return $this->getStreamsOfCustomerId($customerId);
    }

    private function getStoreFrontCustomerId(): ?int
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

    private function getShop(): Shop
    {
        $shop = $this->container->get('shop');
        if (!$shop instanceof Shop) {
            throw new RuntimeException('Shop not initialized correctly');
        }

        return $shop;
    }
}
