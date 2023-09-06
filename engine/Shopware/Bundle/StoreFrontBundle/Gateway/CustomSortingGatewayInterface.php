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

use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface CustomSortingGatewayInterface
{
    /**
     * @param array<int> $ids
     *
     * @return array<int, CustomSorting> indexed by id, sorted by provided id array
     */
    public function getList(array $ids, ShopContextInterface $context);

    /**
     * @param array<int> $categoryIds
     *
     * @return array<int, array<int, CustomSorting>> indexed by category id, sorted by category mapping or position
     */
    public function getSortingsOfCategories(array $categoryIds, ShopContextInterface $context);

    /**
     * @return array<int, CustomSorting> indexed by custom sorting id
     */
    public function getAllCategorySortings(ShopContextInterface $context);
}
