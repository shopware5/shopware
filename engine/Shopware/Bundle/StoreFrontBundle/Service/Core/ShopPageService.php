<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopPageChildrenGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopPageGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ShopPageServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopPage;

class ShopPageService implements ShopPageServiceInterface
{
    private ShopPageGatewayInterface $shopPageGateway;

    private ShopGatewayInterface $shopGateway;

    private ShopPageChildrenGatewayInterface $shopPageChildrenGateway;

    public function __construct(
        ShopPageGatewayInterface $shopPageGateway,
        ShopGatewayInterface $shopGateway,
        ShopPageChildrenGatewayInterface $shopPageChildrenGateway
    ) {
        $this->shopPageGateway = $shopPageGateway;
        $this->shopGateway = $shopGateway;
        $this->shopPageChildrenGateway = $shopPageChildrenGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        $shopPages = $this->shopPageGateway->getList($ids, $context);

        $this->resolveShops($shopPages);
        $this->resolveParents($shopPages, $context);
        $this->resolveChildren($shopPages, $context);

        return $shopPages;
    }

    /**
     * @param array<int, ShopPage> $shopPages
     */
    private function resolveShops(array $shopPages): void
    {
        $shopIds = [];
        foreach ($shopPages as $page) {
            $shopIds += (array) $page->getShopIds();
        }

        $shops = $this->shopGateway->getList(array_keys(array_flip($shopIds)));

        foreach ($shopPages as $page) {
            $pageShops = array_filter($shops, function (Shop $shop) use ($page) {
                return \array_key_exists($shop->getId(), $page->getShopIds());
            });

            $page->setShops($pageShops);
        }
    }

    /**
     * @param array<int, ShopPage> $shopPages
     */
    private function resolveChildren(array $shopPages, ShopContextInterface $context): void
    {
        $ids = array_map(function (ShopPage $page) {
            return $page->getId();
        }, $shopPages);

        $ids = array_unique(array_filter($ids));
        $parentPages = $this->shopPageChildrenGateway->getList($ids, $context);

        foreach ($parentPages as $page) {
            $parentId = $page->getParentId();

            if (!$parentId) {
                continue;
            }

            if (\array_key_exists($parentId, $shopPages)) {
                $shopPages[$parentId]->setChildren(array_merge($shopPages[$parentId]->getChildren(), [$page]));
            }
        }
    }

    /**
     * @param array<int, ShopPage> $shopPages
     */
    private function resolveParents(array $shopPages, ShopContextInterface $context): void
    {
        $parentIds = array_map(function ($page) {
            return $page->getParentId();
        }, $shopPages);

        $parentPages = $this->shopPageGateway->getList($parentIds, $context);

        foreach ($shopPages as $page) {
            if (\array_key_exists($page->getParentId(), $parentPages)) {
                $page->setParent($parentPages[$page->getParentId()]);
            }
        }
    }
}
