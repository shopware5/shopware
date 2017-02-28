<?php
declare(strict_types=1);

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

namespace Shopware\Components\AdvancedMenu;

use Shopware\Bundle\StoreFrontBundle\Service\Core\CategoryDepthService;
use Shopware\Bundle\StoreFrontBundle\Service\Core\CategoryService;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class AdvancedMenuReader
{
    /**
     * @var CategoryDepthService
     */
    private $categoryDepthsService;

    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * AdvancedMenuReader constructor.
     *
     * @param CategoryDepthService $categoryDepthsService
     * @param CategoryService      $categoryService
     */
    public function __construct(
        CategoryDepthService $categoryDepthsService,
        CategoryService $categoryService
    ) {
        $this->categoryDepthsService = $categoryDepthsService;
        $this->categoryService = $categoryService;
    }

    /**
     * @param ShopContextInterface $context
     * @param int                  $depth
     *
     * @return array[]
     */
    public function get(ShopContextInterface $context, int $depth): array
    {
        $ids = $this->categoryDepthsService->get($context->getShop()->getCategory(), $depth);

        $categories = $this->categoryService->getList($ids, $context);

        $categories = json_decode(json_encode($categories), true);

        return $this->buildTree($categories, $context->getShop()->getCategory()->getId());
    }

    /**
     * @param array[] $categories
     * @param int     $parentId
     *
     * @return array[]
     */
    private function buildTree(array $categories, int $parentId): array
    {
        $result = [];
        foreach ($categories as $category) {
            if ($category['parentId'] != $parentId) {
                continue;
            }
            $category['sub'] = $this->buildTree($categories, (int) $category['id']);
            $result[] = $category;
        }

        return $result;
    }
}
