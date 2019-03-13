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

namespace Shopware\Bundle\StoreFrontBundle\Service;

use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface CustomFacetServiceInterface
{
    /**
     * @param int[] $ids
     *
     * @return CustomFacet[]
     */
    public function getList(array $ids, ShopContextInterface $context);

    /**
     * @param int[] $categoryIds
     *
     * @return array indexed by category id, each element contains an array of CustomFacet[]
     */
    public function getFacetsOfCategories(array $categoryIds, ShopContextInterface $context);

    /**
     * @return CustomFacet[] indexed by id, sorted by position
     */
    public function getAllCategoryFacets(ShopContextInterface $context);
}
