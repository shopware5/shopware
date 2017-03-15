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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Service\AdvancedMenuServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryDepthServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\CategoryCollection;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class AdvancedMenuService implements AdvancedMenuServiceInterface
{
    /**
     * @var CategoryDepthService
     */
    private $categoryDepthService;

    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * AdvancedMenuService constructor.
     *
     * @param CategoryDepthServiceInterface $categoryDepthService
     * @param CategoryServiceInterface $categoryService
     */
    public function __construct(
        CategoryDepthServiceInterface $categoryDepthService,
        CategoryServiceInterface $categoryService
    ) {
        $this->categoryDepthService = $categoryDepthService;
        $this->categoryService = $categoryService;
    }

    /**
     * @param ShopContextInterface $context
     * @param int                  $depth
     *
     * @return CategoryCollection
     */
    public function get(ShopContextInterface $context, int $depth): CategoryCollection
    {
        $ids = $this->categoryDepthService->get($context->getShop()->getCategory(), $depth);

        $categories = $this->categoryService->getList($ids, $context);

        return new CategoryCollection($categories);
    }
}
