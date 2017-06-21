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

use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShopPageService implements Service\ShopPageServiceInterface
{
    /**
     * @var Gateway\ShopPageGatewayInterface
     */
    private $shopPageGateway;

    /**
     * @var Gateway\ShopGatewayInterface
     */
    private $shopGateway;

    /**
     * @param Gateway\ShopPageGatewayInterface $shopPageGateway
     * @param Gateway\ShopGatewayInterface     $shopGateway
     */
    public function __construct(Gateway\ShopPageGatewayInterface $shopPageGateway, Gateway\ShopGatewayInterface $shopGateway)
    {
        $this->shopPageGateway = $shopPageGateway;
        $this->shopGateway = $shopGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, Struct\ShopContextInterface $context)
    {
        $shopPages = $this->shopPageGateway->getList($ids, $context);

        $this->resolveShops($shopPages);
        $this->resolveParents($shopPages, $context);
        $this->resolveChildren($shopPages, $context);

        return $shopPages;
    }

    /**
     * @param Struct\ShopPage[] $shopPages
     *
     * @return Struct\ShopPage[]
     */
    private function resolveShops(array $shopPages)
    {
        $shopIds = [];
        foreach ($shopPages as $page) {
            $shopIds += (array) $page->getShopIds();
        }

        $shops = $this->shopGateway->getList(array_keys(array_flip($shopIds)));

        foreach ($shopPages as $page) {
            $pageShops = array_filter($shops, function (Struct\Shop $shop) use ($page) {
                return array_key_exists($shop->getId(), $page->getShopIds());
            });

            $page->setShops($pageShops);
        }
    }

    /**
     * @param Struct\ShopPage[]           $shopPages
     * @param Struct\ShopContextInterface $context
     */
    private function resolveChildren(array $shopPages, Struct\ShopContextInterface $context)
    {
        $parentIds = array_map(function ($page) {
            return $page->getParentId() > 0 ? (int) $page->getId() : null;
        }, $shopPages);

        $parentIds = array_unique(array_filter($parentIds));
        $parentPages = $this->shopPageGateway->getList($parentIds, $context);

        foreach ($parentPages as $page) {
            $parentId = $page->getParentId();

            if (!$parentId) {
                continue;
            }

            if (array_key_exists($parentId, $shopPages)) {
                $shopPages[$parentId]->setChildren(array_merge($shopPages[$parentId]->getChildren(), [$page]));
            }
        }
    }

    /**
     * @param Struct\ShopPage[]           $shopPages
     * @param Struct\ShopContextInterface $context
     */
    private function resolveParents(array $shopPages, Struct\ShopContextInterface $context)
    {
        $parentIds = array_map(function ($page) {
            return $page->getParentId();
        }, $shopPages);

        $parentPages = $this->shopPageGateway->getList($parentIds, $context);

        foreach ($shopPages as $page) {
            if (array_key_exists($page->getParentId(), $parentPages)) {
                $page->setParent($parentPages[$page->getParentId()]);
            }
        }
    }
}
