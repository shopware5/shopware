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

use Shopware\Bundle\StoreFrontBundle\Gateway\CategoryGateway;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CategoryService implements CategoryServiceInterface
{
    /**
     * @var CategoryGateway
     */
    private $categoryGateway;

    /**
     * @param CategoryGateway $categoryGateway
     */
    public function __construct(CategoryGateway $categoryGateway)
    {
        $this->categoryGateway = $categoryGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($ids, ShopContextInterface $context)
    {
        $categories = $this->categoryGateway->getList($ids, $context->getTranslationContext());

        return $this->filterValidCategories($categories, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCategories(array $products, ShopContextInterface $context)
    {
        $categories = $this->categoryGateway->getProductsCategories($products, $context->getTranslationContext());

        $result = [];
        foreach ($categories as $key => $productCategories) {
            $result[$key] = $this->filterValidCategories($productCategories, $context);
        }

        return $result;
    }

    /**
     * @param Category[]           $categories
     * @param ShopContextInterface $context
     *
     * @return Category[] $categories Indexed by the category id
     */
    private function filterValidCategories($categories, ShopContextInterface $context)
    {
        $customerGroup = $context->getCurrentCustomerGroup();

        return array_filter($categories, function (Category $category) use ($customerGroup) {
            return !(in_array($customerGroup->getId(), $category->getBlockedCustomerGroupIds()));
        });
    }
}
