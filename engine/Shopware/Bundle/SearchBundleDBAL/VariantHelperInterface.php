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

namespace Shopware\Bundle\SearchBundleDBAL;

use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface VariantHelperInterface
{
    /**
     * Returns the VariantFacet
     *
     * @return VariantFacet|null
     */
    public function getVariantFacet();

    /**
     * @return void
     */
    public function joinVariantCondition(QueryBuilder $query, VariantCondition $condition);

    /**
     * @return void
     */
    public function joinPrices(QueryBuilder $query, ShopContextInterface $context, Criteria $criteria);

    /**
     * @return void
     */
    public function joinVariants(QueryBuilder $query);
}
