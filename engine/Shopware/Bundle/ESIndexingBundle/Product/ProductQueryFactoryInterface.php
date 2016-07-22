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
namespace Shopware\Bundle\ESIndexingBundle\Product;

use Shopware\Bundle\ESIndexingBundle\LastIdQuery;

/**
 * Class ProductQueryFactoryInterface
 * @package Shopware\Bundle\ESIndexingBundle\Product
 */
interface ProductQueryFactoryInterface
{

    /**
     * @param $categoryId
     * @param null|int $limit
     * @return LastIdQuery
     */
    public function createCategoryQuery($categoryId, $limit = null);

    /**
     * @param int|null $limit
     * @param int[] $priceIds
     * @return LastIdQuery
     */
    public function createPriceIdQuery($priceIds, $limit = null);

    /**
     * @param int|null $limit
     * @param int[] $unitIds
     * @return LastIdQuery
     */
    public function createUnitIdQuery($unitIds, $limit = null);

    /**
     * @param int|null $limit
     * @param int[] $voteIds
     * @return LastIdQuery
     */
    public function createVoteIdQuery($voteIds, $limit = null);

    /**
     * @param int[] $productIds
     * @param null $limit
     * @return LastIdQuery
     */
    public function createProductIdQuery($productIds, $limit = null);

    /**
     * @param int|null $limit
     * @param int[] $variantIds
     * @return LastIdQuery
     */
    public function createVariantIdQuery($variantIds, $limit = null);

    /**
     * @param int|null $limit
     * @param int[] $taxIds
     * @return LastIdQuery
     */
    public function createTaxQuery($taxIds, $limit = null);

    /**
     * @param int|null $limit
     * @param int[] $manufacturerIds
     * @return LastIdQuery
     */
    public function createManufacturerQuery($manufacturerIds, $limit = null);

    /**
     * @param int|null $limit
     * @param int[] $categoryIds
     * @return LastIdQuery
     */
    public function createProductCategoryQuery($categoryIds, $limit = null);

    /**
     * @param int|null $limit
     * @param int[] $groupIds
     * @return LastIdQuery
     */
    public function createPropertyGroupQuery($groupIds, $limit = null);

    /**
     * @param int|null $limit
     * @param int[] $optionIds
     * @return LastIdQuery
     */
    public function createPropertyOptionQuery($optionIds, $limit = null);
}
