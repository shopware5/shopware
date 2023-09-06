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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface CategoryGatewayInterface
{
    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\CategoryGatewayInterface::get()
     *
     * @param array<int> $ids
     *
     * @return Category[] Indexed by the category id
     */
    public function getList(array $ids, ShopContextInterface $context);

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Category requires the following data:
     * - Category base data
     * - Core attribute
     * - Assigned media object
     * - Core attribute of the media object
     *
     * @param int $id
     *
     * @return Category|null
     */
    public function get($id, ShopContextInterface $context);

    /**
     * @param BaseProduct[] $products
     *
     * @return array<string, array<Category>> Indexed by product number, contains all categories of a product
     */
    public function getProductsCategories(array $products, ShopContextInterface $context);
}
