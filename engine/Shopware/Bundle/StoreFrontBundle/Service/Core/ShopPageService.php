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

use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopPageGateway;
use Shopware\Bundle\StoreFrontBundle\Service\ShopPageServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopPage;
use Shopware\Bundle\StoreFrontBundle\Struct\TranslationContext;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShopPageService implements ShopPageServiceInterface
{
    /**
     * @var ShopPageGateway
     */
    private $shopPageGateway;

    /**
     * @var ShopGateway
     */
    private $shopGateway;

    /**
     * @param ShopPageGateway $shopPageGateway
     * @param ShopGateway     $shopGateway
     */
    public function __construct(ShopPageGateway $shopPageGateway, ShopGateway $shopGateway)
    {
        $this->shopPageGateway = $shopPageGateway;
        $this->shopGateway = $shopGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        $shopPages = $this->shopPageGateway->getList($ids, $context->getTranslationContext());

        $this->resolveShops($shopPages, $context->getTranslationContext());
        $this->resolveParents($shopPages, $context);
        $this->resolveChildren($shopPages, $context);

        return $shopPages;
    }

    /**
     * @param ShopPage[] $shopPages
     */
    private function resolveShops(array $shopPages, TranslationContext $context)
    {
        $shopIds = [];
        foreach ($shopPages as $page) {
            $shopIds += (array) $page->getShopIds();
        }

        $shops = $this->shopGateway->getList(array_keys(array_flip($shopIds)), $context);

        foreach ($shopPages as $page) {
            $pageShops = array_filter($shops, function (Shop $shop) use ($page) {
                return array_key_exists($shop->getId(), $page->getShopIds());
            });

            $page->setShops($pageShops);
        }
    }

    /**
     * @param ShopPage[]           $shopPages
     * @param ShopContextInterface $context
     */
    private function resolveChildren(array $shopPages, ShopContextInterface $context)
    {
        $parentIds = array_map(function (ShopPage $page) {
            return $page->getParentId() > 0 ? (int) $page->getId() : null;
        }, $shopPages);

        $parentIds = array_unique(array_filter($parentIds));
        $parentPages = $this->shopPageGateway->getList($parentIds, $context->getTranslationContext());

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
     * @param ShopPage[]           $shopPages
     * @param ShopContextInterface $context
     */
    private function resolveParents(array $shopPages, ShopContextInterface $context)
    {
        $parentIds = array_map(function (ShopPage $page) {
            return $page->getParentId();
        }, $shopPages);

        $parentPages = $this->shopPageGateway->getList($parentIds, $context->getTranslationContext());

        foreach ($shopPages as $page) {
            if (array_key_exists($page->getParentId(), $parentPages)) {
                $page->setParent($parentPages[$page->getParentId()]);
            }
        }
    }
}
